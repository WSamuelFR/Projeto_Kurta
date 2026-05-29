import { Router } from 'express';
import { getAllClans, createClan, joinClan, getClanById, getClanMembers, updateClan, changeRole, removeMember, requestJoinClan, respondJoinClan } from '../controllers/clanController';
import multer from 'multer';
import path from 'path';

const router = Router();

const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, 'public/assets/files/');
  },
  filename: (req, file, cb) => {
    cb(null, 'clan_' + Date.now() + path.extname(file.originalname));
  }
});
const upload = multer({ storage });

router.get('/', getAllClans);
router.get('/:id', getClanById);
router.get('/:id/members', getClanMembers);
router.post('/create', upload.single('clan_pic'), createClan);
router.post('/update', updateClan);
router.post('/join', joinClan);
router.post('/request-join', requestJoinClan);
router.post('/respond-join', respondJoinClan);
router.post('/change-role', changeRole);
router.post('/remove-member', removeMember);

export default router;
