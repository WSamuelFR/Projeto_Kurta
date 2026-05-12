import { Request, Response } from 'express';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import prisma from '../lib/prisma';

export const login = async (req: Request, res: Response) => {
  const { email, password } = req.body;

  try {
    console.log('Tentativa de login para:', email);
    const user = await prisma.user.findFirst({
      where: { email },
      include: { login_login_userTouser: true }
    });

    if (!user) {
      console.log('Usuário não encontrado:', email);
      return res.status(401).json({ status: 'error', message: 'Usuário ou senha incorretos.' });
    }

    if (user.login_login_userTouser.length === 0) {
      console.log('Usuário encontrado mas sem registro de login:', email);
      return res.status(401).json({ status: 'error', message: 'Usuário sem credenciais cadastradas.' });
    }

    const loginData = user.login_login_userTouser[0];
    console.log('Comparando senhas...');
    const isMatch = await bcrypt.compare(password, loginData.password);

    if (!isMatch) {
      console.log('Senha incorreta para:', email);
      return res.status(401).json({ status: 'error', message: 'Usuário ou senha incorretos.' });
    }

    console.log('Gerando token...');
    const token = jwt.sign(
      { userId: user.user_id, email: user.email },
      process.env.JWT_SECRET || 'secret',
      { expiresIn: '7d' }
    );

    console.log('Login bem sucedido:', email);
    res.json({
      status: 'success',
      token,
      user: {
        id: user.user_id,
        first_name: user.first_name,
        last_name: user.last_name,
        profile_pic: user.profile_pic
      }
    });
  } catch (error: any) {
    console.error('ERRO NO LOGIN:', error);
    res.status(500).json({ status: 'error', message: 'Erro interno no servidor: ' + error.message });
  }
};

export const register = async (req: Request, res: Response) => {
  const { first_name, last_name, email, password } = req.body;

  try {
    const existingUser = await prisma.user.findFirst({ where: { email } });
    if (existingUser) {
      return res.status(400).json({ status: 'error', message: 'E-mail já cadastrado.' });
    }

    const hashedPassword = await bcrypt.hash(password, 10);

    const newUser = await prisma.user.create({
      data: {
        first_name,
        last_name,
        email,
        login_login_userTouser: {
          create: {
            password: hashedPassword
          }
        }
      }
    });

    res.json({ status: 'success', message: 'Usuário cadastrado com sucesso!' });
  } catch (error) {
    console.error(error);
    res.status(500).json({ status: 'error', message: 'Erro ao cadastrar usuário.' });
  }
};
