import { Router } from 'express';
import { addFriend, getNotifications, respondFriendRequest, readAllNotifications } from '../controllers/friendshipController';

const router = Router();

router.post('/add-friend', addFriend);
router.get('/notifications', getNotifications);
router.post('/respond-friend', respondFriendRequest);
router.post('/notifications/read-all', readAllNotifications);

export default router;
