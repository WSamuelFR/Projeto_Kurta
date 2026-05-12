import  dotenv from 'dotenv';
dotenv.config();

import express from 'express';
import cors from 'cors';
import authRoutes from './routes/authRoutes';
import feelingRoutes from './routes/feelingRoutes';
import clanRoutes from './routes/clanRoutes';
import profileRoutes from './routes/profileRoutes';
import searchRoutes from './routes/searchRoutes';
import commentRoutes from './routes/commentRoutes';
import socialRoutes from './routes/socialRoutes';

import path from 'path';

// Fix for BigInt serialization
(BigInt.prototype as any).toJSON = function () {
  return this.toString();
};

const app = express();
const PORT = process.env.PORT || 3000;

app.use(cors());
app.use(express.json());
// Servir arquivos estáticos da pasta public/assets na raiz do projeto
app.use('/assets', express.static(path.join(process.cwd(), 'public/assets')));

// Routes
app.use('/api/auth', authRoutes);
app.use('/api/feelings', feelingRoutes);
app.use('/api/clans', clanRoutes);
app.use('/api/profile', profileRoutes);
app.use('/api/search', searchRoutes);
app.use('/api/comments', commentRoutes);
app.use('/api/social', socialRoutes);

app.get('/', (req, res) => {
  res.json({ message: 'fell.it API v2 (TypeScript) is online!' });
});

app.listen(PORT, () => {
  console.log(`Server is running on http://localhost:${PORT}`);
});
