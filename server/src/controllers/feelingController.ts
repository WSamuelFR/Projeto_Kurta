import { Request, Response } from 'express';
import prisma from '../lib/prisma';

export const getGlobalFeed = async (req: Request, res: Response) => {
  const visitorId = parseInt(req.query.visitorId as string) || 0;

  try {
    // 1. Buscar sentimentos
    const feelings = await prisma.feeling.findMany({
      where: {
        AND: [
          { OR: [{ visibility: 'public' }, { visibility: 'publico' }] },
          { cla_id: null }
        ]
      },
      take: 50
    });

    if (feelings.length === 0) return res.json({ status: 'success', data: [] });

    const feelingIds = feelings.map(f => f.feeling_id);
    const userIds = [...new Set(feelings.map(f => f.user))];

    // 2. Buscar dados auxiliares em massa
    const [users, allLikes, allComments, myLikes] = await Promise.all([
      prisma.user.findMany({ where: { user_id: { in: userIds } } }),
      prisma.likes.groupBy({ by: ['feeling_id'], _count: true, where: { feeling_id: { in: feelingIds } } }),
      prisma.coments.groupBy({ by: ['feeling'], _count: true, where: { feeling: { in: feelingIds } } }),
      prisma.likes.findMany({ where: { feeling_id: { in: feelingIds }, user_id: visitorId } })
    ]);

    // 3. Mapear dados
    const userMap = Object.fromEntries(users.map(u => [u.user_id, u]));
    const likesMap = Object.fromEntries(allLikes.map(l => [l.feeling_id, l._count]));
    const commentsMap = Object.fromEntries(allComments.map(c => [c.feeling, c._count]));
    const myLikesSet = new Set(myLikes.map(l => l.feeling_id));

    let formattedFeelings = feelings.map(f => ({
      feeling_id: f.feeling_id,
      feeling: f.feeling,
      visibility: f.visibility,
      created_at: f.created_at,
      user_id: f.user,
      cla_id: f.cla_id,
      first_name: userMap[f.user]?.first_name || 'Usuário',
      last_name: userMap[f.user]?.last_name || 'Desconhecido',
      profile_pic: userMap[f.user]?.profile_pic || null,
      total_likes: likesMap[f.feeling_id] || 0,
      total_comments: commentsMap[f.feeling_id] || 0,
      user_liked: myLikesSet.has(f.feeling_id) ? 1 : 0,
      original_feeling: null,
      original_author_name: null
    }));

    // VOLTAR AO PADRÃO: Mais recentes primeiro
    formattedFeelings.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime());

    res.json({ status: 'success', data: formattedFeelings });
  } catch (error: any) {
    console.error('ERRO NO FEED:', error);
    res.status(500).json({ status: 'error', message: 'Erro de conexão: ' + error.message });
  }
};

export const createFeeling = async (req: Request, res: Response) => {
  const { feeling, visibility, clan_id, user_id } = req.body;
  const u_id = parseInt(user_id);

  if (!feeling || isNaN(u_id)) {
    return res.status(400).json({ status: 'error', message: 'Texto do sentimento e ID de usuário são obrigatórios.' });
  }

  try {
    const newFeeling = await prisma.feeling.create({
      data: {
        feeling,
        user: u_id,
        visibility: visibility || 'public',
        cla_id: clan_id ? parseInt(clan_id) : null
      }
    });
    res.json({ status: 'success', data: newFeeling });
  } catch (error) {
    console.error('Erro ao criar feeling:', error);
    res.status(500).json({ status: 'error', message: 'Erro interno ao postar sentimento.' });
  }
};

export const toggleLike = async (req: Request, res: Response) => {
  const { feeling_id, user_id } = req.body;
  const f_id = parseInt(feeling_id);
  const u_id = parseInt(user_id);

  try {
    const existingLike = await prisma.likes.findFirst({
      where: { feeling_id: f_id, user_id: u_id }
    });

    if (existingLike) {
      await prisma.likes.delete({ where: { like_id: existingLike.like_id } });
      res.json({ status: 'success', action: 'unliked' });
    } else {
      await prisma.likes.create({ data: { feeling_id: f_id, user_id: u_id } });
      res.json({ status: 'success', action: 'liked' });
    }
  } catch (error) {
    res.status(500).json({ status: 'error', message: 'Erro ao processar like.' });
  }
};

export const getUserFeelings = async (req: Request, res: Response) => {
  const { id } = req.params;
  const visitorId = parseInt(req.query.visitorId as string) || 0;
  const u_id = parseInt(id);

  if (isNaN(u_id)) {
    return res.status(400).json({ status: 'error', message: 'ID de usuário inválido.' });
  }

  try {
    // 1. Buscar sentimentos do usuário
    const feelings = await prisma.feeling.findMany({
      where: { user: u_id },
      orderBy: { created_at: 'desc' }
    });

    if (feelings.length === 0) return res.json({ status: 'success', data: [] });

    const feelingIds = feelings.map(f => f.feeling_id);

    // 2. Buscar dados auxiliares em massa
    const [userData, allLikes, allComments, myLikes] = await Promise.all([
      prisma.user.findUnique({ where: { user_id: u_id } }),
      prisma.likes.groupBy({ by: ['feeling_id'], _count: true, where: { feeling_id: { in: feelingIds } } }),
      prisma.coments.groupBy({ by: ['feeling'], _count: true, where: { feeling: { in: feelingIds } } }),
      prisma.likes.findMany({ where: { feeling_id: { in: feelingIds }, user_id: visitorId } })
    ]);

    const likesMap = Object.fromEntries(allLikes.map(l => [l.feeling_id, l._count]));
    const commentsMap = Object.fromEntries(allComments.map(c => [c.feeling, c._count]));
    const myLikesSet = new Set(myLikes.map(l => l.feeling_id));

    const formattedFeelings = feelings.map(f => ({
      feeling_id: f.feeling_id,
      feeling: f.feeling,
      visibility: f.visibility,
      created_at: f.created_at,
      user_id: f.user,
      cla_id: f.cla_id,
      first_name: userData?.first_name || 'Usuário',
      last_name: userData?.last_name || 'Desconhecido',
      profile_pic: userData?.profile_pic || null,
      total_likes: likesMap[f.feeling_id] || 0,
      total_comments: commentsMap[f.feeling_id] || 0,
      user_liked: myLikesSet.has(f.feeling_id) ? 1 : 0,
      original_feeling: null,
      original_author_name: null
    }));

    res.json({ status: 'success', data: formattedFeelings });
  } catch (error: any) {
    console.error('ERRO FATAL NO FEED DO USUÁRIO:', error);
    res.status(500).json({ status: 'error', message: 'Erro ao carregar sentimentos: ' + error.message });
  }
};

export const getClanFeelings = async (req: Request, res: Response) => {
  const { id } = req.params;
  const visitorId = parseInt(req.query.visitorId as string) || 0;
  const c_id = parseInt(id);

  if (isNaN(c_id)) {
    return res.status(400).json({ status: 'error', message: 'ID de clã inválido.' });
  }

  try {
    // 1. Buscar sentimentos do clã
    const feelings = await prisma.feeling.findMany({
      where: { cla_id: c_id },
      orderBy: { created_at: 'desc' }
    });

    if (feelings.length === 0) return res.json({ status: 'success', data: [] });

    const feelingIds = feelings.map(f => f.feeling_id);
    const userIds = [...new Set(feelings.map(f => f.user))];

    // 2. Buscar dados auxiliares em massa
    const [users, allLikes, allComments, myLikes] = await Promise.all([
      prisma.user.findMany({ where: { user_id: { in: userIds } } }),
      prisma.likes.groupBy({ by: ['feeling_id'], _count: true, where: { feeling_id: { in: feelingIds } } }),
      prisma.coments.groupBy({ by: ['feeling'], _count: true, where: { feeling: { in: feelingIds } } }),
      prisma.likes.findMany({ where: { feeling_id: { in: feelingIds }, user_id: visitorId } })
    ]);

    const userMap = Object.fromEntries(users.map(u => [u.user_id, u]));
    const likesMap = Object.fromEntries(allLikes.map(l => [l.feeling_id, l._count]));
    const commentsMap = Object.fromEntries(allComments.map(c => [c.feeling, c._count]));
    const myLikesSet = new Set(myLikes.map(l => l.feeling_id));

    const formattedFeelings = feelings.map(f => ({
      feeling_id: f.feeling_id,
      feeling: f.feeling,
      visibility: f.visibility,
      created_at: f.created_at,
      user_id: f.user,
      cla_id: f.cla_id,
      first_name: userMap[f.user]?.first_name || 'Usuário',
      last_name: userMap[f.user]?.last_name || 'Desconhecido',
      profile_pic: userMap[f.user]?.profile_pic || null,
      total_likes: likesMap[f.feeling_id] || 0,
      total_comments: commentsMap[f.feeling_id] || 0,
      user_liked: myLikesSet.has(f.feeling_id) ? 1 : 0,
      original_feeling: null,
      original_author_name: null
    }));

    res.json({ status: 'success', data: formattedFeelings });
  } catch (error: any) {
    console.error('ERRO FATAL NO FEED DO CLÃ:', error);
    res.status(500).json({ status: 'error', message: 'Erro ao carregar clã: ' + error.message });
  }
};

export const getTrendingFeelings = async (req: Request, res: Response) => {
  try {
    const feelings = await prisma.feeling.findMany({
      where: {
        OR: [{ visibility: 'public' }, { visibility: 'publico' }]
      },
      take: 50 
    });

    const formatted = await Promise.all(feelings.map(async (f) => {
      const userData = await prisma.user.findUnique({ where: { user_id: f.user } });
      const likesCount = await prisma.likes.count({ where: { feeling_id: f.feeling_id } });
      const commentsCount = await prisma.coments.count({ where: { feeling: f.feeling_id } });

      return {
        feeling_id: f.feeling_id,
        feeling: f.feeling,
        created_at: f.created_at,
        first_name: userData?.first_name || 'Usuário',
        last_name: userData?.last_name || 'Desconhecido',
        profile_pic: userData?.profile_pic || null,
        total_likes: likesCount,
        total_comments: commentsCount
      };
    }));

    formatted.sort((a, b) => (b.total_likes + b.total_comments) - (a.total_likes + a.total_comments));
    const top3 = formatted.slice(0, 3);

    res.json({ status: 'success', data: top3 });
  } catch (error: any) {
    console.error('ERRO FATAL NO TRENDING:', error);
    res.status(500).json({ status: 'error', message: 'Erro no trending: ' + error.message });
  }
};
