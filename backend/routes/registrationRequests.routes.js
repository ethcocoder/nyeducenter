const express = require('express');
const router = express.Router();
const regReqCtrl = require('../controllers/registrationRequests.controller');
const auth = require('../middleware/auth');
const roleCheck = require('../middleware/roleCheck');

// Public route - anyone can submit a registration request
router.post('/', regReqCtrl.create);

// Protected routes - only admin can access these
router.get('/', [auth, roleCheck('admin')], regReqCtrl.getAll);
router.post('/:id/approve', [auth, roleCheck('admin')], regReqCtrl.approve);
router.post('/:id/reject', [auth, roleCheck('admin')], regReqCtrl.reject);

module.exports = router; 