import { Request, Response } from 'express';
import prisma from '../lib/prisma';

export const getComments = async (req: Request, res: Response) => {
  const { feeling_id } = req.query;
  const f_id = parseInt(feeling_id as string);
  
  if (isNaN(f_id)) {
    return res.status(400).json({ status: 'error', message: 'ID de postagem inválido.' });
  }

  try {
    const comments = await prisma.coments.findMany({
      where: { feeling: f_id },
      include: {
        user_coments_userTouser: {
          select: { first_name: true, last_name: true, profile_pic: true }
        }
      },
      orderBy: { created_at: 'asc' }
    });

    const formattedComments = comments.map(c => ({
      ...c,
      first_name: c.user_coments_userTouser.first_name,
      last_name: c.user_coments_userTouser.last_name,
      profile_pic: c.user_coments_userTouser.profile_pic
    }));

    res.json({ status: 'success', data: formattedComments });
  } catch (error) {
    console.error(error);
    res.status(500).json({ status: 'error', message: 'Erro ao carregar comentários.' });
  }
};

export const addComment = async (req: Request, res: Response) => {
  const { coment, user_id, feeling_id, parent_id } = req.body;
  
  if (parseInt(user_id) !== (req as any).user.userId) {
    return res.status(403).json({ status: 'error', message: 'Não autorizado.' });
  }
  
  try {
    const newComment = await prisma.coments.create({
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

    const formatted = {
      ...newComment,
      first_name: newComment.user_coments_userTouser.first_name,
      last_name: newComment.user_coments_userTouser.last_name,
      profile_pic: newComment.user_coments_userTouser.profile_pic
    };

    res.json({ status: 'success', data: formatted });
  } catch (error) {
    console.error(error);
    res.status(500).json({ status: 'error', message: 'Erro ao comentar.' });
  }
};
