import { Router } from 'express';
import { addFriend, getNotifications, respondFriendRequest, readAllNotifications, readSingleNotification } from '../controllers/friendshipController';

const router = Router();

router.post('/add-friend', addFriend);
router.get('/notifications', getNotifications);
router.post('/respond-friend', respondFriendRequest);
router.post('/notifications/read-all', readAllNotifications);
router.post('/notifications/read/:id', readSingleNotification);

export default router;
