import { Request, Response } from 'express';
import prisma from '../lib/prisma';

export const addFriend = async (req: Request, res: Response) => {
  const { sender_id, receiver_id } = req.body;
  const senderIdInt = parseInt(sender_id);
  const receiverIdInt = parseInt(receiver_id);
  
  if (senderIdInt !== (req as any).user.userId) {
    return res.status(403).json({ status: 'error', message: 'Não autorizado.' });
  }
  
  try {
    const existing = await prisma.friendship.findFirst({
      where: {
        OR: [
          { sender_id: senderIdInt, receiver_id: receiverIdInt },
          { sender_id: receiverIdInt, receiver_id: senderIdInt }
        ]
      }
    });
    if (existing) return res.json({ status: 'error', message: 'Já existe um pedido ou amizade.' });

    const friendship = await prisma.friendship.create({
      data: { sender_id: senderIdInt, receiver_id: receiverIdInt, status: 'pending' }
    });

    // Criar notificação
    await prisma.notification.create({
      data: {
        user_id: receiverIdInt,
        sender_id: senderIdInt,
        notif_type: 'friend_request',
        reference_id: friendship.friendship_id
      }
    });

    res.json({ status: 'success', message: 'Pedido enviado!' });
  } catch (error) {
    res.status(500).json({ status: 'error', message: 'Erro ao enviar pedido.' });
  }
};

export const getNotifications = async (req: Request, res: Response) => {
  try {
    const authenticatedUserId = (req as any).user.userId;
    const notifications = await prisma.notification.findMany({
      where: { user_id: authenticatedUserId },
      include: {
        user_notification_sender_idTouser: {
          select: { first_name: true, last_name: true, profile_pic: true }
        }
      },
      orderBy: { created_at: 'desc' }
    });
    res.json({ status: 'success', data: notifications });
  } catch (error) {
    res.status(500).json({ status: 'error', message: 'Erro ao carregar notificações.' });
  }
};

export const respondFriendRequest = async (req: Request, res: Response) => {
  const { friendship_id, action, user_id } = req.body;
  
  if (parseInt(user_id) !== (req as any).user.userId) {
    return res.status(403).json({ status: 'error', message: 'Não autorizado.' });
  }
  
  // action: 'accepted' ou 'rejected'
  try {
    const friendship = await prisma.friendship.findUnique({
      where: { friendship_id }
    });

    if (!friendship || friendship.receiver_id !== user_id) {
      return res.status(404).json({ status: 'error', message: 'Convite não encontrado.' });
    }

    if (action === 'accepted') {
      await prisma.friendship.update({
        where: { friendship_id },
        data: { status: 'accepted' }
      });
      
      // Notificar o remetente que foi aceito
      await prisma.notification.create({
        data: {
          user_id: friendship.sender_id,
          sender_id: user_id,
          notif_type: 'friend_accepted'
        }
      });
    } else {
      await prisma.friendship.delete({
        where: { friendship_id }
      });
    }

    res.json({ status: 'success', message: action === 'accepted' ? 'Amizade Consagrada!' : 'Pedido Rejeitado.' });
  } catch (error) {
    res.status(500).json({ status: 'error', message: 'Erro ao processar pedido.' });
  }
};
