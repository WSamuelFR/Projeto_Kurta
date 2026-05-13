import { Request, Response } from 'express';
import prisma from '../lib/prisma';

export const globalSearch = async (req: Request, res: Response) => {
  const { q } = req.query;
  const term = `%${q}%`;

  try {
    const users = await prisma.$queryRaw`SELECT user_id, first_name, last_name, profile_pic FROM users WHERE first_name LIKE ${term} OR last_name LIKE ${term} LIMIT 10`;
    const clans = await prisma.$queryRaw`SELECT clan_id, name_clan, description, clan_pic FROM clan WHERE name_clan LIKE ${term} LIMIT 10`;
    const feelings = await prisma.$queryRaw`
      SELECT f.feeling_id, f.feeling, f.created_at, u.first_name, u.last_name, u.profile_pic 
      FROM feeling f 
      JOIN users u ON f.user = u.user_id 
      WHERE f.feeling LIKE ${term} AND f.visibility = 'public' 
      LIMIT 10
    `;

    res.json({
      status: 'success',
      data: { users, clans, feelings }
    });
  } catch (error) {
    res.status(500).json({ status: 'error', message: 'Erro na busca.' });
  }
};
