import { Request, Response } from 'express';
import prisma from '../lib/prisma';

export const getProfile = async (req: Request, res: Response) => {
  const targetId = parseInt(req.query.target_id as string);
  const myId = parseInt(req.query.my_id as string) || 0;

  console.log(`[PROFILE] Carregando perfil. Target: ${targetId}, MyID: ${myId}`);

  if (isNaN(targetId)) {
    return res.status(400).json({ success: false, message: 'ID de perfil inválido.' });
  }

  try {
    const user = await prisma.user.findUnique({
      where: { user_id: targetId }
    });

    if (!user) {
      return res.status(404).json({ success: false, message: 'Usuário não encontrado.' });
    }

    const safeUser = {
      ...user,
      phone: user.phone ? user.phone.toString() : null
    };

    // Buscar clãs separadamente (Safe Mode)
    const clanMemberships = await prisma.clan_member.findMany({
      where: { user_id: targetId }
    });
    
    const clans = await Promise.all(
      clanMemberships.map(async (cm) => {
        return await prisma.clan.findUnique({ where: { clan_id: cm.clan_id } });
      })
    );

    let friendshipStatus = 'none';
    let friendshipId = null;

    if (myId && myId !== targetId) {
      const friendship = await prisma.friendship.findFirst({
        where: {
          OR: [
            { sender_id: myId, receiver_id: targetId },
            { sender_id: targetId, receiver_id: myId }
          ]
        }
      });

      if (friendship) {
        friendshipId = friendship.friendship_id;
        if (friendship.status === 'accepted') {
          friendshipStatus = 'accepted';
        } else {
          friendshipStatus = friendship.sender_id === myId ? 'pending_sent' : 'pending_received';
        }
      }
    }

    res.json({
      success: true,
      data: safeUser,
      clans: clans.filter(c => c !== null),
      friendship_status: friendshipStatus,
      friendship_id: friendshipId,
      is_own_profile: myId === targetId
    });
  } catch (error: any) {
    console.error('ERRO CRÍTICO NO GETPROFILE:', error);
    res.status(500).json({ success: false, message: 'Erro ao carregar perfil: ' + error.message });
  }
};

export const updateProfile = async (req: Request, res: Response) => {
  const { user_id, first_name, last_name, phone, password } = req.body;
  const id = parseInt(user_id);

  if (isNaN(id)) {
    return res.status(400).json({ success: false, message: 'ID de usuário inválido.' });
  }

  try {
    const updateData: any = { 
      first_name, 
      last_name, 
      phone: phone ? BigInt(phone) : null 
    };
    
    // Se houver nova senha, deveríamos hashear aqui (bcrypt)
    if (password) {
      const existingLogin = await prisma.login.findFirst({ where: { user: id } });
      if (existingLogin) {
        await prisma.login.update({
          where: { login_id: existingLogin.login_id },
          data: { password: password } // TODO: bcrypt
        });
      }
    }

    // Gerenciar arquivos se existirem (req.files)
    const files = req.files as { [fieldname: string]: Express.Multer.File[] };
    if (files?.avatar) {
      updateData.profile_pic = `assets/files/${files.avatar[0].filename}`;
    }
    if (files?.wallpaper) {
      updateData.wallpaper_pic = `assets/files/${files.wallpaper[0].filename}`;
    }

    await prisma.user.update({
      where: { user_id: id },
      data: updateData
    });

    res.json({ success: true, message: 'Perfil atualizado!' });
  } catch (error) {
    console.error('Erro no updateProfile:', error);
    res.status(500).json({ success: false, message: 'Erro interno ao atualizar perfil.' });
  }
};

export const getFriends = async (req: Request, res: Response) => {
  const userId = parseInt(req.query.user_id as string);
  
  if (isNaN(userId)) {
    return res.status(400).json({ status: 'error', message: 'ID de usuário inválido.' });
  }

  try {
    const friendships = await prisma.friendship.findMany({
      where: {
        OR: [{ sender_id: userId }, { receiver_id: userId }],
        status: 'accepted'
      }
    });

    const friendIds = friendships.map(f => 
      f.sender_id === userId ? f.receiver_id : f.sender_id
    );

    const friends = await prisma.user.findMany({
      where: { user_id: { in: friendIds } }
    });

    const safeFriends = friends.map(u => ({
      ...u,
      phone: u.phone ? u.phone.toString() : null
    }));

    res.json({ status: 'success', data: safeFriends });
  } catch (error: any) {
    console.error('ERRO AO CARREGAR AMIGOS:', error);
    res.status(500).json({ status: 'error', message: 'Erro ao carregar amigos: ' + error.message });
  }
};

export const removeFriend = async (req: Request, res: Response) => {
  const { user_id, friend_id } = req.body;
  try {
    await prisma.friendship.deleteMany({
      where: {
        OR: [
          { sender_id: user_id, receiver_id: friend_id },
          { sender_id: friend_id, receiver_id: user_id }
        ]
      }
    });
    res.json({ status: 'success', message: 'Amigo removido.' });
  } catch (error) {
    res.status(500).json({ status: 'error', message: 'Erro ao remover amigo.' });
  }
};
