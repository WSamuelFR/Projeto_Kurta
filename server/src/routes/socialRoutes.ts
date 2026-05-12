import { Router } from 'express';
import { addFriend, getNotifications, respondFriendRequest } from '../controllers/friendshipController';

const router = Router();

router.post('/add-friend', addFriend);
router.get('/notifications', getNotifications);
router.post('/respond-friend', respondFriendRequest);

export default router;
