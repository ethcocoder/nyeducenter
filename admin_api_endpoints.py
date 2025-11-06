# ===================================
# NEW ADMIN DASHBOARD API ENDPOINTS
# These should be inserted into app.py before the reorder_modules function
# ===================================

# @app.route("/admin/module/create", methods=['POST'])
# @login_required
# def create_module():
#     if current_user.role != 'admin':
#         return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
#     
#     data = request.get_json()
#     title = data.get('title', '').strip()
#     
#     if not title:
#         return jsonify({'status': 'error', 'message': 'Title cannot be empty'}), 400
#     
#     try:
#         last_module = Module.query.order_by(Module.order.desc()).first()
#         new_order = last_module.order + 1 if last_module else 1
#         
#         new_module = Module(title=title, order=new_order, is_published=False)
#         db.session.add(new_module)
#         db.session.commit()
#         
#         return jsonify({'status': 'success', 'message': 'Module created', 'module_id': new_module.id})
#     except Exception as e:
#         db.session.rollback()
#         return jsonify({'status': 'error', 'message': str(e)}), 500
# 
# @app.route("/admin/module/<int:module_id>/toggle-publish", methods=['POST'])
# @login_required
# def toggle_module_publish(module_id):
#     if current_user.role != 'admin':
#         return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
#     
#     module = Module.query.get_or_404(module_id)
#     module.is_published = not module.is_published
#     db.session.commit()
#     
#     return jsonify({'status': 'success', 'message': 'Publish status toggled', 'is_published': module.is_published})
# 
# @app.route("/admin/module/<int:module_id>/duplicate", methods=['POST'])
# @login_required
# def duplicate_module(module_id):
#     if current_user.role != 'admin':
#         return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
#     
#     original_module = Module.query.get_or_404(module_id)
#     
#     try:
#         # Create new module
#         last_module = Module.query.order_by(Module.order.desc()).first()
#         new_order = last_module.order + 1 if last_module else 1
#         
#         new_module = Module(
#             title=original_module.title + ' (Copy)',
#             order=new_order,
#             is_published=False
#         )
#         db.session.add(new_module)
#         db.session.flush()
#         
#         # Duplicate submodules and items
#         for submodule in original_module.submodules:
#             new_submodule = Submodule(
#                 title=submodule.title,
#                 order=submodule.order,
#                 module_id=new_module.id
#             )
#             db.session.add(new_submodule)
#             db.session.flush()
#             
#             for item in submodule.items:
#                 new_item = ModuleItem(
#                     order=item.order,
#                     submodule_id=new_submodule.id,
#                     content_type=item.content_type,
#                     content_id=item.content_id
#                 )
#                 db.session.add(new_item)
#         
#         db.session.commit()
#         return jsonify({'status': 'success', 'message': 'Module duplicated'})
#     except Exception as e:
#         db.session.rollback()
#         return jsonify({'status': 'error', 'message': str(e)}), 500
# 
# @app.route("/admin/submodule/create", methods=['POST'])
# @login_required
# def create_submodule():
#     if current_user.role != 'admin':
#         return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
#     
#     data = request.get_json()
#     module_id = data.get('module_id')
#     title = data.get('title', '').strip()
#     
#     if not module_id or not title:
#         return jsonify({'status': 'error', 'message': 'Module ID and title are required'}), 400
#     
#     try:
#         module = Module.query.get_or_404(module_id)
#         last_submodule = Submodule.query.filter_by(module_id=module_id).order_by(Submodule.order.desc()).first()
#         new_order = last_submodule.order + 1 if last_submodule else 1
#         
#         new_submodule = Submodule(title=title, order=new_order, module_id=module_id)
#         db.session.add(new_submodule)
#         db.session.commit()
#         
#         return jsonify({'status': 'success', 'message': 'Submodule created', 'submodule_id': new_submodule.id})
#     except Exception as e:
#         db.session.rollback()
#         return jsonify({'status': 'error', 'message': str(e)}), 500
# 
# @app.route("/admin/submodule/<int:submodule_id>/duplicate", methods=['POST'])
# @login_required
# def duplicate_submodule(submodule_id):
#     if current_user.role != 'admin':
#         return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
#     
#     original_submodule = Submodule.query.get_or_404(submodule_id)
#     
#     try:
#         last_submodule = Submodule.query.filter_by(module_id=original_submodule.module_id).order_by(Submodule.order.desc()).first()
#         new_order = last_submodule.order + 1 if last_submodule else 1
#         
#         new_submodule = Submodule(
#             title=original_submodule.title + ' (Copy)',
#             order=new_order,
#             module_id=original_submodule.module_id
#         )
#         db.session.add(new_submodule)
#         db.session.flush()
#         
#         # Duplicate items
#         for item in original_submodule.items:
#             new_item = ModuleItem(
#                 order=item.order,
#                 submodule_id=new_submodule.id,
#                 content_type=item.content_type,
#                 content_id=item.content_id
#             )
#             db.session.add(new_item)
#         
#         db.session.commit()
#         return jsonify({'status': 'success', 'message': 'Submodule duplicated'})
#     except Exception as e:
#         db.session.rollback()
#         return jsonify({'status': 'error', 'message': str(e)}), 500
# 
# @app.route("/admin/item/create", methods=['POST'])
# @login_required
# def create_content_item():
#     if current_user.role != 'admin':
#         return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
#     
#     data = request.get_json()
#     submodule_id = data.get('submodule_id')
#     content_type = data.get('content_type')
#     content_id = data.get('content_id')
#     
#     if not all([submodule_id, content_type, content_id]):
#         return jsonify({'status': 'error', 'message': 'Missing required fields'}), 400
#     
#     try:
#         submodule = Submodule.query.get_or_404(submodule_id)
#         last_item = ModuleItem.query.filter_by(submodule_id=submodule_id).order_by(ModuleItem.order.desc()).first()
#         new_order = last_item.order + 1 if last_item else 1
#         
#         new_item = ModuleItem(
#             order=new_order,
#             submodule_id=submodule_id,
#             content_type=content_type,
#             content_id=int(content_id)
#         )
#         db.session.add(new_item)
#         db.session.commit()
#         
#         return jsonify({'status': 'success', 'message': 'Content item created', 'item_id': new_item.id})
#     except Exception as e:
#         db.session.rollback()
#         return jsonify({'status': 'error', 'message': str(e)}), 500
# 
# @app.route("/admin/content/list/<content_type>")
# @login_required
# def list_content_by_type(content_type):
#     if current_user.role != 'admin':
#         return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
#     
#     try:
#         if content_type == 'quiz':
#             items = Quiz.query.order_by(Quiz.title).all()
#         elif content_type == 'lab':
#             items = Lab.query.order_by(Lab.title).all()
#         elif content_type == 'session':
#             items = PracticalSession.query.order_by(PracticalSession.title).all()
#         else:
#             return jsonify({'status': 'error', 'message': 'Invalid content type'}), 400
#         
#         return jsonify({
#             'status': 'success',
#             'items': [{'id': item.id, 'title': item.title} for item in items]
#         })
#     except Exception as e:
#         return jsonify({'status': 'error', 'message': str(e)}), 500
