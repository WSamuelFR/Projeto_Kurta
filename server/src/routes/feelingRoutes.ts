import { Router } from 'express';
import { getGlobalFeed, createFeeling, toggleLike, getUserFeelings, getClanFeelings, getTrendingFeelings } from '../controllers/feelingController';

const router = Router();

router.get('/global', getGlobalFeed);
router.get('/trending', getTrendingFeelings);
router.get('/user/:id', getUserFeelings);
router.get('/clan/:id', getClanFeelings);
router.post('/create', createFeeling);
router.post('/like', toggleLike);

export default router;
