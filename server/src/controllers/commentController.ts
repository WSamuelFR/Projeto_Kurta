import { Request, Response } from 'express';
import prisma from '../lib/prisma';

export const getComments = async (req: Request, res: Response) => {
  const { feeling_id, is_clan } = req.query;
  const f_id = parseInt(feeling_id as string);
  const isClan = is_clan === 'true';
  
  if (isNaN(f_id)) {
    return res.status(400).json({ status: 'error', message: 'ID de postagem inválido.' });
  }

  try {
    const comments = isClan
      ? await prisma.clan_coments.findMany({
          where: { feeling: f_id },
          include: {
            user_clan_coments_userTouser: {
              select: { first_name: true, last_name: true, profile_pic: true }
            }
          },
          orderBy: { created_at: 'asc' }
        })
      : await prisma.coments.findMany({
          where: { feeling: f_id },
          include: {
            user_coments_userTouser: {
              select: { first_name: true, last_name: true, profile_pic: true }
            }
          },
          orderBy: { created_at: 'asc' }
        });

    const formattedComments = comments.map(c => {
      const user = isClan ? (c as any).user_clan_coments_userTouser : (c as any).user_coments_userTouser;
      return {
        ...c,
        first_name: user.first_name,
        last_name: user.last_name,
        profile_pic: user.profile_pic
      };
    });

    res.json({ status: 'success', data: formattedComments });
  } catch (error) {
    console.error(error);
    res.status(500).json({ status: 'error', message: 'Erro ao carregar comentários.' });
  }
};

export const addComment = async (req: Request, res: Response) => {
  const { coment, user_id, feeling_id, parent_id, is_clan } = req.body;
  const isClan = !!is_clan;
  
  if (parseInt(user_id) !== (req as any).user.userId) {
    return res.status(403).json({ status: 'error', message: 'Não autorizado.' });
  }
  
  try {
    const newComment = isClan
      ? await prisma.clan_coments.create({
          data: {
            coment,
            user: parseInt(user_id),
            feeling: parseInt(feeling_id),
            parent_id: parent_id ? parseInt(parent_id) : null
          },
          include: {
            user_clan_coments_userTouser: {
              select: { first_name: true, last_name: true, profile_pic: true }
            }
          }
        })
      : await prisma.coments.create({
          data: {
            coment,
            user: parseInt(user_id),
            feeling: parseInt(feeling_id),
            parent_id: parent_id ? parseInt(parent_id) : null
          },
          include: {
            user_coments_userTouser: {
              select: { first_name: true, last_name: true, profile_pic: true }
            }
          }
        });

    const user = isClan ? (newComment as any).user_clan_coments_userTouser : (newComment as any).user_coments_userTouser;
    const formatted = {
      ...newComment,
      first_name: user.first_name,
      last_name: user.last_name,
      profile_pic: user.profile_pic
    };

    res.json({ status: 'success', data: formatted });
  } catch (error) {
    console.error(error);
    res.status(500).json({ status: 'error', message: 'Erro ao comentar.' });
  }
};
