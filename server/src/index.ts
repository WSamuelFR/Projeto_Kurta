import  dotenv from 'dotenv';
dotenv.config();

import express, { Request, Response } from 'express';
import cors from 'cors';
import authRoutes from './routes/authRoutes';
import feelingRoutes from './routes/feelingRoutes';
import clanRoutes from './routes/clanRoutes';
import profileRoutes from './routes/profileRoutes';
import searchRoutes from './routes/searchRoutes';
import commentRoutes from './routes/commentRoutes';
import socialRoutes from './routes/socialRoutes';

import path from 'path';
import fs from 'fs';

// Fix for BigInt serialization
(BigInt.prototype as any).toJSON = function () {
  return this.toString();
};

import prisma from './lib/prisma';

const app = express();
const PORT = process.env.PORT || 3000;

// Teste de conexão com o banco na inicialização
console.log('[SERVER] Verificando conexão com o banco de dados...');
prisma.$connect()
  .then(() => console.log('[SERVER] Conexão com o banco de dados estabelecida com sucesso!'))
  .catch((err) => {
    console.error('[SERVER] ERRO CRÍTICO: Falha ao conectar ao banco de dados:', err.message);
    process.exit(1); // Encerra o processo se não conseguir conectar
  });

app.use(cors());
app.use(express.json());
// Servir arquivos estáticos da pasta public/assets na raiz do projeto
app.use('/assets', express.static(path.join(process.cwd(), 'public/assets')));

// Determinar o caminho da pasta dist (suporta dev e prod instalado)
const distPath = fs.existsSync(path.join(process.cwd(), 'dist')) 
  ? path.join(process.cwd(), 'dist') 
  : path.join(process.cwd(), '..', 'dist');

console.log(`[SERVER] Usando pasta dist em: ${distPath}`);

// Servir arquivos estáticos do frontend (após build)
app.use(express.static(distPath));

// Routes
app.use('/api/auth', authRoutes);
app.use('/api/feelings', feelingRoutes);
app.use('/api/clans', clanRoutes);
app.use('/api/profile', profileRoutes);
app.use('/api/search', searchRoutes);
app.use('/api/comments', commentRoutes);
app.use('/api/social', socialRoutes);

// Catch-all para SPA: Usa Regex para capturar tudo (compatível com Express 5)
app.get(/.*/, (req: Request, res: Response) => {
  if (req.path.startsWith('/api')) {
    return res.status(404).json({ error: 'Endpoint não encontrado' });
  }
  const indexPath = path.join(distPath, 'index.html');
  if (fs.existsSync(indexPath)) {
    res.sendFile(indexPath);
  } else {
    res.status(404).send('Frontend não encontrado. Certifique-se de rodar npm run build.');
  }
});

app.listen(PORT, () => {
  console.log(`Server is running on http://localhost:${PORT}`);
});
