import { Request, Response } from 'express';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import prisma from '../lib/prisma';

export const login = async (req: Request, res: Response) => {
  const { email, password } = req.body;

  try {
    console.log('Tentativa de login para:', email);
    const user = await prisma.users.findFirst({
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
  const { first_name, last_name, email, password, phone } = req.body;

  try {
    // Verificação manual de e-mail existente
    const existingUser = await prisma.users.findFirst({ where: { email } });
    if (existingUser) {
      return res.status(400).json({ status: 'error', message: 'Este e-mail já está cadastrado.' });
    }

    const hashedPassword = await bcrypt.hash(password, 10);

    // Tratamento do telefone para BigInt (remove tudo que não for número)
    let phoneDigits: bigint | null = null;
    if (phone) {
      const cleanedPhone = phone.replace(/\D/g, '');
      if (cleanedPhone) {
        phoneDigits = BigInt(cleanedPhone);
      }
    }

    const newUser = await prisma.users.create({
      data: {
        first_name,
        last_name,
        email,
        phone: phoneDigits,
        login_login_userTouser: {
          create: {
            password: hashedPassword
          }
        }
      }
    });

    res.json({ status: 'success', message: 'Usuário cadastrado com sucesso!' });
  } catch (error: any) {
    console.error('Erro no registro:', error);
    
    let message = 'Erro ao cadastrar usuário.';
    
    if (error.code === 'P2002') {
      const target = error.meta?.target || '';
      if (target.includes('email')) {
        message = 'Este e-mail já está em uso.';
      } else if (target.includes('phone')) {
        message = 'Este telefone já está em uso.';
      } else {
        message = 'Alguns dados informados já constam no sistema.';
      }
      return res.status(400).json({ status: 'error', message });
    }

    res.status(500).json({ 
      status: 'error', 
      message,
      detail: process.env.NODE_ENV === 'development' ? error.message : undefined
    });
  }
};
