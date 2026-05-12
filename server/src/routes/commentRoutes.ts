import { Router } from 'express';
import { getComments, addComment } from '../controllers/commentController';

const router = Router();

router.get('/', getComments);
router.post('/add', addComment);

export default router;
