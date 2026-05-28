import { Request, Response } from 'express';
import prisma from '../lib/prisma';

export const getAllClans = async (req: Request, res: Response) => {
  const { user_id, filter } = req.query;
  let u_id = user_id ? parseInt(user_id as string) : null;

  if (filter === 'meus-clas') {
    u_id = (req as any).user.userId;
  }

  try {
    const where: any = {};
    if (filter === 'meus-clas' && u_id) {
      where.clan_member = {
        some: { user_id: u_id }
      };
    }

    const clans = await prisma.clan.findMany({
      where,
      include: {
        clan_member: u_id ? {
          where: { user_id: u_id }
        } : false,
        _count: {
          select: { clan_member: true }
        }
      }
    });

    const formattedClans = clans.map(c => ({
      ...c,
      total_membros: c._count.clan_member,
      user_role: c.clan_member?.[0]?.role || null
    }));

    res.json({ status: 'success', data: formattedClans });
  } catch (error) {
    console.error(error);
    res.status(500).json({ status: 'error', message: 'Erro ao carregar clãs.' });
  }
};

export const createClan = async (req: Request, res: Response) => {
  const { name_clan, description, user_id, visibility } = req.body;
  const u_id = parseInt(user_id);

  if (!name_clan || isNaN(u_id)) {
    return res.status(400).json({ status: 'error', message: 'Nome do clã e ID de usuário são obrigatórios.' });
  }

  if (u_id !== (req as any).user.userId) {
    return res.status(403).json({ status: 'error', message: 'Não autorizado.' });
  }

  try {
    const clanData: any = {
      name_clan,
      description,
      visibility: visibility || 'public'
    };

    if (req.file) {
      clanData.clan_pic = `assets/files/${req.file.filename}`;
    }

    const newClan = await prisma.clan.create({
      data: {
        ...clanData,
        clan_member: {
          create: {
            user_id: u_id,
            role: 'rei'
          }
        }
      }
    });

    res.json({ status: 'success', data: newClan });
  } catch (error) {
    console.error('Erro ao criar clã:', error);
    res.status(500).json({ status: 'error', message: 'Erro interno ao criar clã.' });
  }
};

export const joinClan = async (req: Request, res: Response) => {
  const { clan_id, user_id } = req.body;
  const c_id = parseInt(clan_id);
  const u_id = parseInt(user_id);

  if (isNaN(c_id) || isNaN(u_id)) {
    return res.status(400).json({ status: 'error', message: 'Dados de identificação inválidos.' });
  }

  if (u_id !== (req as any).user.userId) {
    return res.status(403).json({ status: 'error', message: 'Não autorizado.' });
  }

  try {
    const existing = await prisma.clan_member.findFirst({
      where: { clan_id: c_id, user_id: u_id }
    });

    if (existing) {
      return res.status(400).json({ status: 'error', message: 'Você já é membro deste clã.' });
    }

    const clan = await prisma.clan.findUnique({ where: { clan_id: c_id } });
    if (!clan) return res.status(404).json({ status: 'error', message: 'Clã não encontrado.' });

    if (clan.visibility === 'private') {
      return res.status(403).json({ status: 'error', message: 'Este clã é privado.' });
    }

    await prisma.clan_member.create({
      data: { clan_id: c_id, user_id: u_id, role: 'aldeao' }
    });

    res.json({ status: 'success', message: 'Bem-vindo ao Clã!' });
  } catch (error) {
    console.error(error);
    res.status(500).json({ status: 'error', message: 'Erro interno ao entrar no clã.' });
  }
};

export const getClanById = async (req: Request, res: Response) => {
  const { id } = req.params;
  const c_id = parseInt(id as string);

  if (isNaN(c_id)) {
    return res.status(400).json({ status: 'error', message: 'ID do clã inválido.' });
  }

  try {
    const clan = await prisma.clan.findUnique({
      where: { clan_id: c_id },
      include: {
        _count: {
          select: { clan_member: true }
        }
      }
    });
    if (!clan) return res.status(404).json({ status: 'error', message: 'Clã não encontrado.' });
    res.json({ status: 'success', data: clan });
  } catch (error) {
    console.error(error);
    res.status(500).json({ status: 'error', message: 'Erro ao carregar clã.' });
  }
};

export const getClanMembers = async (req: Request, res: Response) => {
  const { id } = req.params;
  const { user_id } = req.query;
  const c_id = parseInt(id as string);

  if (isNaN(c_id)) {
    return res.status(400).json({ status: 'error', message: 'ID do clã inválido.' });
  }

  try {
    const members = await prisma.clan_member.findMany({
      where: { clan_id: c_id },
      include: {
        user: {
          select: {
            user_id: true,
            first_name: true,
            last_name: true,
            profile_pic: true
          }
        }
      }
    });

    let viewerRole = null;
    const authenticatedUserId = (req as any).user.userId;
    const viewer = members.find(m => m.user_id === authenticatedUserId);
    if (viewer) viewerRole = viewer.role;

    const formattedMembers = members.map(m => ({
      ...m.user,
      role: m.role
    }));

    res.json({ status: 'success', data: formattedMembers, viewerRole });
  } catch (error) {
    console.error(error);
    res.status(500).json({ status: 'error', message: 'Erro ao carregar membros.' });
  }
};

export const updateClan = async (req: Request, res: Response) => {
  const { clan_id, name_clan, description, visibility } = req.body;
  const c_id = parseInt(clan_id);

  if (isNaN(c_id)) return res.status(400).json({ status: 'error', message: 'ID do clã inválido.' });

  try {
    const authenticatedUserId = (req as any).user.userId;
    const member = await prisma.clan_member.findFirst({
      where: { clan_id: c_id, user_id: authenticatedUserId }
    });
    if (!member || member.role !== 'rei') {
      return res.status(403).json({ status: 'error', message: 'Apenas o Rei do clã pode atualizar os dados.' });
    }

    const updated = await prisma.clan.update({
      where: { clan_id: c_id },
      data: { name_clan, description, visibility }
    });
    res.json({ status: 'success', data: updated });
  } catch (error) {
    res.status(500).json({ status: 'error', message: 'Erro ao atualizar clã.' });
  }
};

export const changeRole = async (req: Request, res: Response) => {
  const { clan_id, target_user_id, new_role } = req.body;
  const c_id = parseInt(clan_id);
  const u_id = parseInt(target_user_id);

  try {
    const authenticatedUserId = (req as any).user.userId;
    const member = await prisma.clan_member.findFirst({
      where: { clan_id: c_id, user_id: authenticatedUserId }
    });
    if (!member || member.role !== 'rei') {
      return res.status(403).json({ status: 'error', message: 'Apenas o Rei do clã pode alterar cargos.' });
    }

    await prisma.clan_member.updateMany({
      where: { clan_id: c_id, user_id: u_id },
      data: { role: new_role }
    });
    res.json({ status: 'success', message: 'Cargo atualizado!' });
  } catch (error) {
    res.status(500).json({ status: 'error', message: 'Erro ao mudar cargo.' });
  }
};

export const removeMember = async (req: Request, res: Response) => {
  const { clan_id, target_user_id } = req.body;
  const c_id = parseInt(clan_id);
  const u_id = parseInt(target_user_id);

  try {
    const authenticatedUserId = (req as any).user.userId;
    if (u_id !== authenticatedUserId) {
      const member = await prisma.clan_member.findFirst({
        where: { clan_id: c_id, user_id: authenticatedUserId }
      });
      if (!member || member.role !== 'rei') {
        return res.status(403).json({ status: 'error', message: 'Apenas o Rei do clã pode remover membros.' });
      }
    }

    await prisma.clan_member.deleteMany({
      where: { clan_id: c_id, user_id: u_id }
    });
    res.json({ status: 'success', message: 'Membro removido.' });
  } catch (error) {
    res.status(500).json({ status: 'error', message: 'Erro ao remover membro.' });
  }
};
