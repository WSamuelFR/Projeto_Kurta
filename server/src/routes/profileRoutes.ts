import { Router } from 'express';
import { getProfile, updateProfile, getFriends, removeFriend } from '../controllers/profileController';
import multer from 'multer';
import path from 'path';

const router = Router();

// Configuração básica do Multer para uploads de perfil
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    cb(null, 'public/assets/files/');
  },
  filename: (req, file, cb) => {
    cb(null, Date.now() + path.extname(file.originalname));
  }
});
const upload = multer({ storage });

router.get('/', getProfile);
router.post('/update', upload.fields([{ name: 'avatar' }, { name: 'wallpaper' }]), updateProfile);
router.get('/friends', getFriends);
router.post('/remove-friend', removeFriend);

export default router;
