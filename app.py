import click
import os
import datetime
import yaml
from flask import request, jsonify, send_from_directory
from bs4 import BeautifulSoup
from werkzeug.utils import secure_filename

# ... (rest of your imports) ...
import json
import markdown
from flask import Flask, render_template, url_for, flash, redirect, jsonify, request # <-- ADD request
from flask_sqlalchemy import SQLAlchemy
from flask_bcrypt import Bcrypt
from flask_login import LoginManager, UserMixin, login_user, current_user, logout_user, login_required
from flask_migrate import Migrate
from flask_wtf import FlaskForm
from wtforms import StringField, PasswordField, SubmitField, BooleanField, TextAreaField
from wtforms.validators import DataRequired, Length, Email, EqualTo, ValidationError
from flask_wtf.file import FileField, FileAllowed


# ===================================
# 1. APP INITIALIZATION & CONFIG
# ===================================

app = Flask(__name__)

# Configuration
app.config['SECRET_KEY'] = os.urandom(24)
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///site.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False
app.config['UPLOAD_FOLDER'] = 'static/uploads/profile_pics'

# Initialize Extensions
db = SQLAlchemy(app)
bcrypt = Bcrypt(app)
login_manager = LoginManager(app)
login_manager.login_view = 'login'
login_manager.login_message_category = 'info'
migrate = Migrate(app, db)

# Quiz Categories
QUIZ_CATEGORIES = {
    'quiz': {'name': 'Quiz', 'questions': 3, 'passing_score': 3},
    'test': {'name': 'Test', 'questions': 15, 'passing_score': 13},
    'exam': {'name': 'Exam', 'questions': 40, 'passing_score': 28}
}

# ===================================
# 4. HELPER FUNCTIONS
# ===================================

def get_user_progress_map(user_id):
    """
    Returns a dictionary mapping module_item_id to its status for a given user.
    """
    progress_records = UserProgress.query.filter_by(user_id=user_id).all()
    return {record.module_item_id: record.status for record in progress_records}

def calculate_module_progress(module, user_progress_map):
    """
    Calculates the completion percentage for a given module based on user progress.
    """
    total_items = 0
    completed_items = 0

    for submodule in module.submodules:
        for item in submodule.items:
            total_items += 1
            if user_progress_map.get(item.id) == 'completed':
                completed_items += 1
    
    if total_items == 0:
        return 0
    return round((completed_items / total_items) * 100)

def parse_quiz_markdown(markdown_text):
    """
    Parses a string of markdown text and returns a list of question dictionaries.
    Returns None if there is a parsing error.
    """
    # ... (the full code for this function)
    questions = []
    question_blocks = markdown_text.strip().split('# Question ')
    
    for block in question_blocks:
        if not block.strip():
            continue
        try:
            lines = block.strip().split('\n')
            question_line = lines[0]
            question_text = question_line.split(':', 1)[1].strip()
            options = [line.split(':', 1)[1].strip() for line in lines[1:5]]
            answer_line = lines[5]
            answer_str = answer_line.split('option')[1].strip()
            correct_option_index = int(answer_str) - 1
            if not (0 <= correct_option_index < 4):
                return None
            question_data = {
                'question_text': question_text,
                'options': options,
                'correct_index': correct_option_index
            }
            questions.append(question_data)
        except (IndexError, ValueError) as e:
            print(f"Error parsing block: {e}")
            return None
    return questions
   
def parse_lab_markdown(markdown_text):
    """
    Parses a string of lab markdown text with strict validation.
    Returns a tuple: (list_of_steps, error_message).
    On success, error_message is None.
    On failure, list_of_steps is None.
    """
    steps = []
    # Split the text into individual step blocks
    step_blocks = markdown_text.strip().split('Step ')
    
    for block in step_blocks:
        if not block.strip():
            continue

        try:
            # First line is the step number and description
            first_line, *other_lines = block.strip().split('\n')
            step_number_str, description_text = first_line.split(':', 1)
            step_number = int(step_number_str.strip())
            description_text = description_text.strip()

            # Find Type and match lines
            type_line = next((line for line in other_lines if line.strip().startswith('Type:')), None)
            match_line = next((line for line in other_lines if line.strip().startswith('- match:')), None)

            # --- STRICT VALIDATION ---
            if type_line is None:
                return None, f"Validation Error in Step {step_number}: 'Type:' tag is missing."
            if match_line is None:
                return None, f"Validation Error in Step {step_number}: '- match:' tag is missing."
            
            # Extract and strip whitespace from both ends
            type_text = type_line.split(':', 1)[1].strip()
            match_text = match_line.split(':', 1)[1].strip()

            # Check if fields are empty AFTER stripping
            if not type_text:
                return None, f"Validation Error in Step {step_number}: 'Type:' cannot be empty or just whitespace."
            if not match_text:
                 return None, f"Validation Error in Step {step_number}: '- match:' cannot be empty or just whitespace."

            # Compare the cleaned-up values
            if type_text != match_text:
                return None, f"Validation Error in Step {step_number}: 'Type:' and '- match:' values do not match. Got '{type_text}' and '{match_text}'."
            
            # --- END OF VALIDATION ---

            step_data = {
                'step_number': step_number,
                'description_text': description_text,
                'type_text': type_text,
                'match_text': match_text
            }
            steps.append(step_data)

        except (ValueError, IndexError) as e:
            return None, f"Formatting Error: A step block is malformed. Please check the structure around: '{block[:50]}...'. Error: {e}"

    # Final check: ensure steps are sequential
    for i, step in enumerate(steps):
        if step['step_number'] != i + 1:
            return None, f"Sequence Error: Steps are not in sequential order. Expected step {i+1} but found {step['step_number']}."
            
    return steps, None # Success
    
# app.py -> In the HELPER FUNCTIONS section

def parse_session_yaml(yaml_text):
    """
    Parses a YAML string for a Practical Session.
    Returns a tuple: (parsed_data, error_message).
    """
    try:
        data = yaml.safe_load(yaml_text)
    except yaml.YAMLError as e:
        return None, f"YAML Formatting Error: {e}"

    # --- Structure Validation ---
    if not isinstance(data, dict):
        return None, "Validation Error: The root of the YAML file must be a dictionary."

    title = data.get('title')
    if not title or not isinstance(title, str):
        return None, "Validation Error: A 'title' string is required at the root of the file."

    requirements = data.get('requirements')
    if not requirements or not isinstance(requirements, list):
        return None, "Validation Error: A 'requirements' list is required."

    # --- Requirement Field Validation ---
    for i, req in enumerate(requirements, 1):
        if not isinstance(req, dict):
            return None, f"Validation Error in Requirement #{i}: Each requirement must be a dictionary."
        if 'description' not in req or not isinstance(req['description'], str):
            return None, f"Validation Error in Requirement #{i}: A 'description' string is required."
        if 'check_type' not in req or not isinstance(req['check_type'], str):
            return None, f"Validation Error in Requirement #{i}: A 'check_type' string is required."

    return {'title': title, 'requirements': requirements}, None # Success

def save_picture(form_picture):
    random_hex = os.urandom(8).hex()
    _, f_ext = os.path.splitext(form_picture.filename)
    picture_fn = random_hex + f_ext
    picture_path = os.path.join(app.root_path, app.config['UPLOAD_FOLDER'], picture_fn)
    
    # Save the file directly without resizing
    form_picture.save(picture_path)

    return picture_fn

def update_user_progress_and_unlock(user, content_type, content_id):
    """
    Finds a module item, marks it as complete for the user, and checks
    if the parent module can be unlocked.
    """
    # Find the module item in the course structure
    module_item = ModuleItem.query.filter_by(content_type=content_type, content_id=content_id).first()
    
    # If this content isn't in the course, there's nothing to do.
    if not module_item:
        return

    # Find or create a progress record for this user and this item
    progress_record = UserProgress.query.filter_by(user_id=user.id, module_item_id=module_item.id).first()
    if not progress_record:
        progress_record = UserProgress(user_id=user.id, module_item_id=module_item.id)
        db.session.add(progress_record)
    
    # If it's already complete, we don't need to re-process everything.
    if progress_record.status == 'completed':
        db.session.commit() # Commit in case a new record was added
        return
        
    progress_record.status = 'completed'
    db.session.commit()

    # --- MODULE COMPLETION & CERTIFICATE LOGIC ---
    parent_module = module_item.submodule.module
    
    all_item_ids = [
        item.id for item in ModuleItem.query.join(Submodule)
        .filter(Submodule.module_id == parent_module.id).all()
    ]
    
    completed_items_count = UserProgress.query.filter(
        UserProgress.user_id == user.id,
        UserProgress.module_item_id.in_(all_item_ids),
        UserProgress.status == 'completed'
    ).count()

    # Check if the module is now 100% complete
    if len(all_item_ids) > 0 and completed_items_count == len(all_item_ids):
        # --- MODULE UNLOCKING ---
        # Only unlock the *next* module if the user just completed their *current* one.
        if user.current_module_order == parent_module.order:
            user.current_module_order += 1
            db.session.commit() # Commit module order change
    
# app.py -> In the HELPER FUNCTIONS section
def validate_html_code(user_html, requirements):
    """
    Validates a user's HTML code against a list of requirement objects.
    Returns a list of dictionaries, each indicating the pass/fail status and a message.
    """
    validation_results = []
    
    if not isinstance(user_html, str) or not user_html.strip():
        return [{'description': 'HTML content provided', 'passed': False, 'message': 'No HTML content provided for validation.'}]
    
    try:
        soup = BeautifulSoup(user_html, 'html.parser')
    except Exception as e:
        # Catch parsing errors from BeautifulSoup
        return [{'description': 'HTML parsing', 'passed': False, 'message': f'Failed to parse HTML: {e}'}]

    for req in requirements:
        check_type = req.check_type
        selector = req.selector
        attr_name = req.attribute_name
        expected_value = req.value
        is_valid = False
        message = f"Requirement '{req.description}' failed."

        try:
            if check_type == "doctype_exists":
                doctype = soup.find(text=lambda text: isinstance(text, str) and text.lower().startswith('<!doctype'))
                if doctype and expected_value and expected_value.lower() in doctype.lower():
                    is_valid = True
                    message = f"Doctype '{expected_value}' found."
                elif not doctype:
                    message = "Doctype declaration not found."
                else:
                    message = f"Doctype found but does not match '{expected_value}'."
            
            elif check_type == "element_exists":
                element = soup.select_one(selector)
                if element:
                    is_valid = True
                    message = f"Element '{selector}' found."
                else:
                    message = f"Element '{selector}' not found."

            elif check_type == "element_count":
                elements = soup.select(selector)
                if elements and expected_value is not None:
                    try:
                        expected_count = int(expected_value)
                        if len(elements) == expected_count:
                            is_valid = True
                            message = f"Found {expected_count} elements matching '{selector}'."
                        else:
                            message = f"Expected {expected_count} elements matching '{selector}', but found {len(elements)}."
                    except ValueError:
                        message = f"Invalid expected_value for element_count: '{expected_value}' is not an integer."
                else:
                    message = f"No elements matching '{selector}' found or expected count not specified."
            
            elif check_type == "attribute_exists":
                element = soup.select_one(selector)
                if element and attr_name:
                    if element.has_attr(attr_name):
                        if expected_value is not None:
                            if element[attr_name] == expected_value:
                                is_valid = True
                                message = f"Element '{selector}' has attribute '{attr_name}' with value '{expected_value}'."
                            else:
                                message = f"Element '{selector}' has attribute '{attr_name}' but its value is '{element[attr_name]}' not '{expected_value}'."
                        else:
                            is_valid = True
                            message = f"Element '{selector}' has attribute '{attr_name}'."
                    else:
                        message = f"Element '{selector}' does not have attribute '{attr_name}'."
                else:
                    message = f"Element '{selector}' not found or attribute name not specified."
            
            elif check_type == "element_has_text":
                element = soup.select_one(selector)
                if element and expected_value is not None:
                    if element.get_text(strip=True) == expected_value:
                        is_valid = True
                        message = f"Element '{selector}' contains text '{expected_value}'."
                    else:
                        message = f"Element '{selector}' contains text '{element.get_text(strip=True)}' but expected '{expected_value}'."
                else:
                    message = f"Element '{selector}' not found or expected text not specified."
            
            # Add more HTML-specific checks here as needed
            # elif check_type == "element_contains_child":
            #     ...

        except Exception as e:
            message = f"An error occurred during validation of '{req.description}': {e}"
            is_valid = False # Ensure it's marked as failed if an exception occurs during check

        validation_results.append({
            'description': req.description,
            'passed': is_valid,
            'message': message
        })
            
    return validation_results

# --- Placeholder for future CSS validation ---
def validate_css_code(user_css, requirements):
    """
    Placeholder for validating a user's CSS code against a list of requirement objects.
    This function would integrate specialized CSS parsing and validation tools.
    """
    # Example:
    # from css_parser import CSSParser # Hypothetical CSS parser library
    # parser = CSSParser()
    # stylesheet = parser.parse(user_css)
    #
    # validation_results = []
    # for req in requirements:
    #     # Logic to check CSS rules, properties, values, etc.
    #     # using the parsed stylesheet
    #     is_valid = False
    #     message = "CSS validation not yet implemented."
    #     validation_results.append({
    #         'description': req.description,
    #         'passed': is_valid,
    #         'message': message
    #     })
    # return validation_results
    return [{'description': r.description, 'passed': False, 'message': 'CSS validation not yet implemented.'} for r in requirements]

# --- Placeholder for future JavaScript validation ---
def validate_javascript_code(user_js, requirements):
    """
    Placeholder for validating a user's JavaScript code against a list of requirement objects.
    This function would integrate specialized JavaScript parsing and validation tools.
    """
    return [{'description': r.description, 'passed': False, 'message': 'JavaScript validation not yet implemented.'} for r in requirements]

# --- Placeholder for future Backend (Node.js) validation ---
def validate_backend_code(user_backend_files, requirements):
    """
    Placeholder for validating a user's backend code (e.g., Node.js) against a list of requirement objects.
    This would involve static analysis, potentially running tests, or deploying in a sandbox.
    """
    return [{'description': r.description, 'passed': False, 'message': 'Backend validation not yet implemented.'} for r in requirements]

# app.py -> API ROUTES section    
        
# app.py -> API ROUTES section
@app.route("/api/lab/check_step", methods=['POST'])
@login_required
def check_lab_step():
    data = request.get_json()
    try:
        step_id = int(data.get('step_id'))
        user_input = data.get('user_input', '')
    except (TypeError, ValueError):
        return jsonify({'error': 'Invalid or missing data'}), 400

    step = LabStep.query.get(step_id)
    if not step:
        return jsonify({'error': 'Step not found'}), 404

    cleaned_user_input = user_input.strip()
    is_correct = (cleaned_user_input == step.match_text)

    if is_correct:
        progress = LabProgress.query.filter_by(user_id=current_user.id, lab_id=step.lab_id).first()
        if progress and progress.current_step_number == step.step_number:
            progress.current_step_number += 1
            db.session.commit()
        
        # --- NEW COMPLETION LOGIC ---
        total_steps = len(step.lab.steps) # Get total steps from the relationship
        
        # Check if the step they just finished was the last one
        if step.step_number >= total_steps:
            # --- NEW PROGRESS HOOK ---
            # User has just completed the final step of the lab.
            update_user_progress_and_unlock(current_user, 'lab', step.lab_id)
            # --- END OF HOOK ---
            next_url = url_for('lab_complete', lab_id=step.lab_id)
        else:
            # Otherwise, generate the URL for the next step
            next_step_number = step.step_number + 1
            next_url = url_for('lab_step_viewer', lab_id=step.lab_id, step_number=next_step_number)
        
        return jsonify({'correct': True, 'next_url': next_url})
        # --- END OF NEW LOGIC ---
    else:
        return jsonify({'correct': False, 'message': 'Input does not match. Please check for typos and try again.'})

# app.py -> API ROUTES section

@app.route("/api/session/validate", methods=['POST'])
@login_required
def validate_session():
    data = request.get_json()
    session_id = data.get('session_id')
    user_code = data.get('user_code')

    if not session_id or user_code is None:
        return jsonify({'error': 'Missing session_id or user_code'}), 400

    session = PracticalSession.query.get(session_id)
    if not session:
        return jsonify({'error': 'Session not found'}), 404
        
    # Use our powerful validator function from Subtask 4.2
    results = validate_html_code(user_code, session.requirements)
    
    # --- NEW PROGRESS HOOK LOGIC ---
    # `all(result['passed'])` will be True only if every item in the list is True.
    is_complete = all(result['passed'] for result in results)
    
    if is_complete:
        update_user_progress_and_unlock(current_user, 'session', session_id)
    
    # Return both the individual results and the overall completion status
    return jsonify({'results': results, 'is_complete': is_complete})
# app.py -> ROUTES section

# ... (after lab_list route)

@app.route("/sessions")
@login_required
def session_list():
    sessions = PracticalSession.query.order_by(PracticalSession.id.asc()).all()
    return render_template('session_list.html', sessions=sessions)

@app.route("/session/<int:session_id>")
@login_required
def session_viewer(session_id):
    session = PracticalSession.query.get_or_404(session_id)
    return render_template('session_viewer.html', session=session)

@app.route("/session/<int:session_id>/complete")
@login_required
def session_complete(session_id):
    session = PracticalSession.query.get_or_404(session_id)
    return render_template('session_complete.html', session=session)

@app.route("/notes")
@login_required
def note_list():
    notes = Note.query.order_by(Note.id.asc()).all()
    return render_template('note_list.html', notes=notes)

@app.route("/note/<int:note_id>")
@login_required
def note_viewer(note_id):
    note = Note.query.get_or_404(note_id)
    return render_template('note_viewer.html', note=note)

@app.route("/module/<int:module_id>")
@login_required
def module_viewer(module_id):
    module = Module.query.get_or_404(module_id)
    user_progress_map = get_user_progress_map(current_user.id)
    return render_template('module_viewer.html', module=module, user_progress_map=user_progress_map)

@app.route("/submodule/<int:submodule_id>")
@login_required
def submodule_viewer(submodule_id):
    submodule = Submodule.query.get_or_404(submodule_id)
    user_progress_map = get_user_progress_map(current_user.id)
    return render_template('submodule_viewer.html', submodule=submodule, user_progress_map=user_progress_map)

@app.route('/certificate/<int:certificate_id>')
@login_required
def view_certificate(certificate_id):
    certificate = Certificate.query.get_or_404(certificate_id)
    # Security check: ensure the current user is the owner of the certificate
    if certificate.user_id != current_user.id:
        flash('You are not authorized to view this certificate.', 'danger')
        return redirect(url_for('course_dashboard'))
    return render_template('certificate.html', certificate=certificate)

@app.route('/certificate/<int:certificate_id>/download')
@login_required
def download_certificate(certificate_id):
    certificate = Certificate.query.get_or_404(certificate_id)
    if certificate.user_id != current_user.id:
        flash('You are not authorized to download this certificate.', 'danger')
        return redirect(url_for('course_dashboard'))
    
    # Placeholder for PDF generation
    flash('PDF export functionality is coming soon!', 'info')
    return redirect(url_for('view_certificate', certificate_id=certificate.id))

@app.route('/module/<int:module_id>/check-completion', methods=['POST'])
@login_required
def check_module_completion(module_id):
    module = Module.query.get_or_404(module_id)
    user_progress_map = get_user_progress_map(current_user.id)
    progress_percent = calculate_module_progress(module, user_progress_map)

    if progress_percent == 100:
        # Check if a certificate has already been earned
        existing_certificate = Certificate.query.filter_by(user_id=current_user.id, module_id=module.id).first()
        if existing_certificate:
            flash('You have already earned a certificate for this module.', 'info')
            return redirect(url_for('view_certificate', certificate_id=existing_certificate.id))
        else:
            return redirect(url_for('confirm_certificate_page', module_id=module.id))
    else:
        flash(f'You have not completed the module yet. Your current progress is {progress_percent}%. Keep going!', 'warning')
        return redirect(url_for('module_viewer', module_id=module.id))

@app.route('/module/<int:module_id>/confirm-certificate')
@login_required
def confirm_certificate_page(module_id):
    module = Module.query.get_or_404(module_id)
    return render_template('confirm_certificate.html', module=module)

@app.route('/create-certificate', methods=['POST'])
@login_required
def create_certificate():
    module_id = request.form.get('module_id')
    certificate_name = request.form.get('certificate_name')

    if not module_id or not certificate_name:
        flash('Missing module ID or certificate name.', 'danger')
        return redirect(url_for('course_dashboard'))

    module = Module.query.get_or_404(module_id)

    # Check if a certificate already exists for this user and module
    existing_certificate = Certificate.query.filter_by(user_id=current_user.id, module_id=module.id).first()
    if existing_certificate:
        flash('You have already earned a certificate for this module.', 'info')
        return redirect(url_for('view_certificate', certificate_id=existing_certificate.id))

    new_certificate = Certificate(
        certificate_name=certificate_name,
        user_id=current_user.id,
        module_id=module.id
    )
    db.session.add(new_certificate)
    db.session.commit()
    flash('Your certificate has been successfully generated!', 'success')
    return redirect(url_for('view_certificate', certificate_id=new_certificate.id))

@app.route("/profile/settings", methods=['GET', 'POST'])
@login_required
def profile_settings():
    profile_form = ProfileForm()
    password_form = PasswordForm()

    if profile_form.validate_on_submit() and request.method == 'POST' and 'profile_update' in request.form:
        if profile_form.picture.data:
            picture_file = save_picture(profile_form.picture.data)
            current_user.profile_image_file = picture_file
        current_user.full_name = profile_form.full_name.data
        current_user.bio = profile_form.bio.data
        db.session.commit()
        flash('Your profile has been updated!', 'success')
        return redirect(url_for('profile_settings'))
    elif request.method == 'GET':
        profile_form.full_name.data = current_user.full_name
        profile_form.bio.data = current_user.bio
    
    return render_template('profile_settings.html', profile_form=profile_form, password_form=password_form)

@app.route("/profile/change-password", methods=['POST'])
@login_required
def change_password():
    password_form = PasswordForm()
    if password_form.validate_on_submit() and request.method == 'POST' and 'password_update' in request.form:
        if bcrypt.check_password_hash(current_user.password_hash, password_form.old_password.data):
            hashed_password = bcrypt.generate_password_hash(password_form.new_password.data).decode('utf-8')
            current_user.password_hash = hashed_password
            db.session.commit()
            flash('Your password has been updated!', 'success')
            return redirect(url_for('profile_settings'))
        else:
            flash('Incorrect current password.', 'danger')
    return redirect(url_for('profile_settings'))



# ... (rest of your routes)

# ===================================
# 4.5 API ROUTES
# ===================================

@app.route("/api/quiz/check_answer", methods=['POST'])
@login_required
def check_answer():
    data = request.get_json()
    question_id = data.get('question_id')
    option_id = data.get('option_id')

    # Basic validation
    if not question_id or not option_id:
        return jsonify({'error': 'Missing data'}), 400

    selected_option = Option.query.get(option_id)
    if not selected_option or selected_option.question_id != question_id:
        return jsonify({'error': 'Invalid option'}), 404

    is_correct = selected_option.is_correct

    # If the answer is wrong, we need to send back the ID of the correct answer
    # so the frontend can highlight it for the user.
    if not is_correct:
        correct_option = Option.query.filter_by(question_id=question_id, is_correct=True).first()
        return jsonify({'correct': False, 'correct_option_id': correct_option.id})
    else:
        return jsonify({'correct': True, 'correct_option_id': selected_option.id})
# app.py

# ... (in your API ROUTES section) ...

@app.route("/api/quiz/submit_result", methods=['POST'])
@login_required
def submit_result():
    data = request.get_json()
    quiz_id = data.get('quiz_id')
    score = data.get('score')
    
    quiz = Quiz.query.get(quiz_id)
    if not quiz:
        return jsonify({'error': 'Quiz not found'}), 404

    passed = score >= quiz.passing_score

    # Create a new attempt record
    attempt = QuizAttempt(
        score=score,
        passed=passed,
        user_id=current_user.id,
        quiz_id=quiz_id
    )
    db.session.add(attempt)
    db.session.commit()
    
    if passed:
        update_user_progress_and_unlock(current_user, 'quiz', quiz_id)
    # --- END OF HOOK ---
    
    return jsonify({'status': 'success', 'passed': passed})

# ===================================
# 6. CUSTOM CLI COMMANDS
# ===================================
# ... rest of your file ...

# ===================================
# 6. CUSTOM CLI COMMANDS  <-- THIS SECTION MUST COME AFTER THE HELPER FUNCTION
# ===================================

@app.cli.command("create-admin")
# ... (create-admin function) ...

@app.cli.command("process-quiz")
@click.argument("filepath")
def process_quiz(filepath):
    # ... (the full code for this function)
    # This can now safely call parse_quiz_markdown because it was defined above.
    print(f"Attempting to process quiz file: {filepath}")
    try:
        with open(filepath, 'r') as f:
            content = f.read()
    except FileNotFoundError:
        print(f"Error: File not found at '{filepath}'")
        return

    parsed_data = parse_quiz_markdown(content) # This will work now
    # ... (rest of the function)
    if not parsed_data:
        print("Error: Parsing failed. Please check the file format. Aborting.")
        return
    
    # ... (rest of the database logic) ...


# ===================================
# 7. APP EXECUTION
# ===================================
# ... (if __name__ == '__main__':) ...

# ===================================
# 2. DATABASE MODELS
# ===================================

# The UserMixin provides default implementations for the methods that Flask-Login expects.
# app.py

# ===================================
# 2. DATABASE MODELS
# ===================================

# app.py -> In the DATABASE MODELS section (Find your User model)

class User(db.Model, UserMixin):
    __tablename__ = 'user'
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(20), unique=True, nullable=False)
    email = db.Column(db.String(120), unique=True, nullable=False)
    password_hash = db.Column(db.String(60), nullable=False)
    role = db.Column(db.String(10), nullable=False, default='user')
    
    # -- Profile Fields --
    full_name = db.Column(db.String(100), nullable=True)
    bio = db.Column(db.Text, nullable=True)
    profile_image_file = db.Column(db.String(20), nullable=False, default='default.jpg')
    
    # --- ADD THIS COLUMN FOR MODULE LOCKING ---
    current_module_order = db.Column(db.Integer, nullable=False, default=1)
    
    # --- Relationships ---
    attempts = db.relationship('QuizAttempt', backref='attempting_user', lazy=True)
    lab_progress = db.relationship('LabProgress', backref='progressing_user', lazy=True, cascade="all, delete-orphan")
    
    # --- ADD THIS RELATIONSHIP FOR COURSE PROGRESS ---
    progress_records = db.relationship('UserProgress', backref='user', lazy=True, cascade="all, delete-orphan")
    
    # --- ADD THIS RELATIONSHIP FOR CERTIFICATES ---
    certificates = db.relationship('Certificate', backref='user', lazy=True)


    def __repr__(self):
        return f"User('{self.username}', '{self.email}', '{self.role}')"

# app.py -> In the DATABASE MODELS section
# ===================================
# COURSE STRUCTURE MODELS
# ===================================

class Course(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(150), nullable=False)
    is_published = db.Column(db.Boolean, nullable=False, default=False)

    def __repr__(self):
        return f"Course('{self.title}')"


class Module(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(150), nullable=False)
    order = db.Column(db.Integer, nullable=False, unique=True)
    is_published = db.Column(db.Boolean, nullable=False, default=False)
    
    # Relationship to Submodules
    submodules = db.relationship('Submodule', back_populates='module', lazy='dynamic', cascade="all, delete-orphan", order_by='Submodule.order')
    
    # --- ADD THIS RELATIONSHIP FOR CERTIFICATES ---
    certificates = db.relationship('Certificate', backref='module', lazy=True, cascade="all, delete-orphan")

    def __repr__(self):
        return f"Module('{self.title}', Order: {self.order})"

class Submodule(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(150), nullable=False)
    order = db.Column(db.Integer, nullable=False)
    
    # Foreign Key to Module
    module_id = db.Column(db.Integer, db.ForeignKey('module.id'), nullable=True)
    
    # Self-referential foreign key for nested submodules
    parent_id = db.Column(db.Integer, db.ForeignKey('submodule.id'), nullable=True)
    
    # Relationships
    module = db.relationship('Module', back_populates='submodules')
    items = db.relationship('ModuleItem', back_populates='submodule', lazy='dynamic', cascade="all, delete-orphan", order_by='ModuleItem.order')
    
    # Relationship to parent submodule
    parent = db.relationship('Submodule', remote_side=[id], backref=db.backref('children', lazy='dynamic'))

    def __repr__(self):
        return f"Submodule('{self.title}', Order: {self.order})"

class ModuleItem(db.Model):
    __tablename__ = 'module_item'
    id = db.Column(db.Integer, primary_key=True)
    order = db.Column(db.Integer, nullable=False)
    
    # Foreign Key to Submodule
    submodule_id = db.Column(db.Integer, db.ForeignKey('submodule.id'), nullable=False)
    
    # Polymorphic Content Fields
    content_type = db.Column(db.String(50), nullable=False)  # 'quiz', 'lab', 'session'
    content_id = db.Column(db.Integer, nullable=False)
    
    # Relationships
    submodule = db.relationship('Submodule', back_populates='items')
    user_progress = db.relationship('UserProgress', backref='module_item', lazy=True, cascade="all, delete-orphan")
    
    def __repr__(self):
        return f"ModuleItem(Type: '{self.content_type}', ID: {self.content_id})"

    @property
    def content_object(self):
        if self.content_type == 'quiz':
            return Quiz.query.get(self.content_id)
        elif self.content_type == 'lab':
            return Lab.query.get(self.content_id)
        elif self.content_type == 'session':
            return PracticalSession.query.get(self.content_id)
        elif self.content_type == 'note':
            return Note.query.get(self.content_id)
        return None

class UserProgress(db.Model):
    __tablename__ = 'user_progress'
    id = db.Column(db.Integer, primary_key=True)
    status = db.Column(db.String(20), nullable=False, default='not_started') # e.g., 'not_started', 'completed'
    last_updated = db.Column(db.DateTime, nullable=False, default=datetime.datetime.utcnow, onupdate=datetime.datetime.utcnow)
    
    # Foreign Keys
    user_id = db.Column(db.Integer, db.ForeignKey('user.id'), nullable=False)
    module_item_id = db.Column(db.Integer, db.ForeignKey('module_item.id'), nullable=False)
    
    # A user can only have one progress entry per module item
    __table_args__ = (db.UniqueConstraint('user_id', 'module_item_id', name='uq_user_module_item_progress'),)

    def __repr__(self):
        return f"UserProgress(User: {self.user_id}, Item: {self.module_item_id}, Status: '{self.status}')"

class Certificate(db.Model):
    __tablename__ = 'certificate'
    id = db.Column(db.Integer, primary_key=True)
    certificate_name = db.Column(db.String(100), nullable=False)
    completion_date = db.Column(db.DateTime, nullable=False, default=datetime.datetime.utcnow)
    
    # Foreign Keys
    user_id = db.Column(db.Integer, db.ForeignKey('user.id'), nullable=False)
    module_id = db.Column(db.Integer, db.ForeignKey('module.id'), nullable=False)
    
    # A user can only have one certificate per module
    __table_args__ = (db.UniqueConstraint('user_id', 'module_id', name='uq_user_module_certificate'),)

    def __repr__(self):
        return f"Certificate(User: {self.user_id}, Module: {self.module_id})"

@login_manager.user_loader
def load_user(user_id):
    return User.query.get(int(user_id))

# --- PASTE THE NEW MODELS BELOW ---

class Quiz(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(100), nullable=False)
    filename = db.Column(db.String(100), unique=True, nullable=False)
    category = db.Column(db.String(50), nullable=False, default='Quiz')
    passing_score = db.Column(db.Integer, nullable=False, default=18)
    questions = db.relationship('Question', backref='quiz', lazy=True, cascade="all, delete-orphan")
    attempts = db.relationship('QuizAttempt', backref='quiz', lazy=True, cascade="all, delete-orphan")

    def __repr__(self):
        return f"Quiz('{self.title}')"

class Question(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    question_text = db.Column(db.String(500), nullable=False)
    quiz_id = db.Column(db.Integer, db.ForeignKey('quiz.id'), nullable=False)
    options = db.relationship('Option', backref='question', lazy=True, cascade="all, delete-orphan")

    def __repr__(self):
        return f"Question('{self.question_text[:30]}...')"

class Option(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    option_text = db.Column(db.String(200), nullable=False)
    is_correct = db.Column(db.Boolean, nullable=False, default=False)
    question_id = db.Column(db.Integer, db.ForeignKey('question.id'), nullable=False)

    def __repr__(self):
        return f"Option('{self.option_text}', Correct: {self.is_correct})"

class QuizAttempt(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    score = db.Column(db.Integer, nullable=False)
    passed = db.Column(db.Boolean, nullable=False)
    timestamp = db.Column(db.DateTime, nullable=False, default=datetime.datetime.utcnow)
    user_id = db.Column(db.Integer, db.ForeignKey('user.id'), nullable=False)
    quiz_id = db.Column(db.Integer, db.ForeignKey('quiz.id'), nullable=False)

    def __repr__(self):
        return f"Attempt(User: {self.user_id}, Quiz: {self.quiz_id}, Score: {self.score})"

class Lab(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(100), nullable=False)
    filename = db.Column(db.String(100), unique=True, nullable=False)
    # Relationships
    steps = db.relationship('LabStep', backref='lab', lazy=True, cascade="all, delete-orphan")
    progress_records = db.relationship('LabProgress', backref='in_progress_lab', lazy=True, cascade="all, delete-orphan")

    def __repr__(self):
        return f"Lab('{self.title}')"

class LabStep(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    step_number = db.Column(db.Integer, nullable=False)
    description_text = db.Column(db.Text, nullable=False)
    type_text = db.Column(db.String(500), nullable=False)
    match_text = db.Column(db.String(500), nullable=False)
    # Foreign Key to Lab
    lab_id = db.Column(db.Integer, db.ForeignKey('lab.id'), nullable=False)

    def __repr__(self):
        return f"LabStep({self.step_number} for Lab ID: {self.lab_id})"

class LabProgress(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    current_step_number = db.Column(db.Integer, nullable=False, default=1)
    last_updated = db.Column(db.DateTime, nullable=False, default=datetime.datetime.utcnow, onupdate=datetime.datetime.utcnow)
    # Foreign Keys
    user_id = db.Column(db.Integer, db.ForeignKey('user.id'), nullable=False)
    lab_id = db.Column(db.Integer, db.ForeignKey('lab.id'), nullable=False)
    # A user can only have one progress entry per lab
    __table_args__ = (db.UniqueConstraint('user_id', 'lab_id', name='uq_user_lab_progress'),)

    def __repr__(self):
        return f"LabProgress(User: {self.user_id}, Lab: {self.lab_id}, Step: {self.current_step_number})"

class PracticalSession(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(100), nullable=False)
    filename = db.Column(db.String(100), unique=True, nullable=False)
    
    # Relationship
    requirements = db.relationship('Requirement', back_populates='session', lazy=True, cascade="all, delete-orphan")

    def __repr__(self):
        return f"PracticalSession('{self.title}')"

class Requirement(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    description = db.Column(db.Text, nullable=False)
    check_type = db.Column(db.String(50), nullable=False)
    selector = db.Column(db.String(200), nullable=True) # Nullable as some checks might not need it
    attribute_name = db.Column(db.String(50), nullable=True)
    value = db.Column(db.String(500), nullable=True)
    
    # Foreign Key
    session_id = db.Column(db.Integer, db.ForeignKey('practical_session.id'), nullable=False)
    
    # Relationship
    session = db.relationship('PracticalSession', back_populates='requirements')

    def __repr__(self):
        return f"Requirement('{self.check_type}' for Session ID: {self.session_id})"

class Note(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    title = db.Column(db.String(100), nullable=False)
    filename = db.Column(db.String(100), unique=True, nullable=False)
    content = db.Column(db.Text, nullable=False)
    quiz_id = db.Column(db.Integer, db.ForeignKey('quiz.id'), nullable=False, unique=True)
    quiz = db.relationship('Quiz', backref=db.backref('note_assoc', uselist=False))

    def __repr__(self):
        return f"Note('{self.title}')"

# ===================================
# 3. FORMS
# ===================================
# ... rest of your file


# ===================================
# 3. FORMS
# ===================================


class RegistrationForm(FlaskForm):
    username = StringField('Username', 
                           validators=[DataRequired(), Length(min=2, max=20)])
    email = StringField('Email', 
                        validators=[DataRequired(), Email()])
    password = PasswordField('Password', validators=[DataRequired()])
    confirm_password = PasswordField('Confirm Password', 
                                     validators=[DataRequired(), EqualTo('password')])
    submit = SubmitField('Sign Up')

    # Custom validator to check if username is already taken
    def validate_username(self, username):
        user = User.query.filter_by(username=username.data).first()
        if user:
            raise ValidationError('That username is taken. Please choose a different one.')

    # Custom validator to check if email is already taken
    def validate_email(self, email):
        user = User.query.filter_by(email=email.data).first()
        if user:
            raise ValidationError('That email is already in use. Please choose a different one.')

class LoginForm(FlaskForm):
    username = StringField('Username', 
                           validators=[DataRequired(), Length(min=2, max=20)])
    password = PasswordField('Password', validators=[DataRequired()])
    remember = BooleanField('Remember Me')
    submit = SubmitField('Login')


class ProfileForm(FlaskForm):
    full_name = StringField('Full Name', validators=[Length(max=100)])
    bio = TextAreaField('About Me', validators=[Length(max=500)])
    picture = FileField('Update Profile Picture', validators=[FileAllowed(['jpg', 'png', 'jpeg'])])
    submit = SubmitField('Update Profile')

class PasswordForm(FlaskForm):
    old_password = PasswordField('Current Password', validators=[DataRequired()])
    new_password = PasswordField('New Password', validators=[DataRequired(), Length(min=6)])
    confirm_new_password = PasswordField('Confirm New Password', validators=[DataRequired(), EqualTo('new_password')])
    submit = SubmitField('Change Password')


# ===================================
# 4. ROUTES
# ===================================
@app.route("/")
def index():
    # --- NEW: Check if the user is already logged in ---
    if current_user.is_authenticated:
        # Redirect to the appropriate dashboard based on role
        if current_user.role == 'admin':
            return redirect(url_for('admin_dashboard'))
    
    # If not logged in, show the regular landing page
    return render_template('index.html')

# ... (rest of your routes) ...

@app.route("/signup", methods=['GET', 'POST'])
def signup():
    if current_user.is_authenticated:
        return redirect(url_for('index'))
    form = RegistrationForm()
    if form.validate_on_submit():
        hashed_password = bcrypt.generate_password_hash(form.password.data).decode('utf-8')
        user = User(username=form.username.data, email=form.email.data, password_hash=hashed_password)
        db.session.add(user)
        db.session.commit()
        flash('Your account has been created! You are now able to log in.', 'success')
        return redirect(url_for('login'))
    return render_template('signup.html', form=form)

@app.route("/login", methods=['GET', 'POST'])
def login():
    if current_user.is_authenticated:
        return redirect(url_for('index'))
    form = LoginForm()
    if form.validate_on_submit():
        user = User.query.filter_by(username=form.username.data).first()
        if user and bcrypt.check_password_hash(user.password_hash, form.password.data):
            login_user(user, remember=form.remember.data)
            if user.role == 'admin':
                return redirect(url_for('admin_dashboard'))
            else:
                return redirect(url_for('course_dashboard'))
        else:
            flash('Login Unsuccessful. Please check email and password.', 'danger')
    return render_template('login.html', form=form)

@app.route("/course")
@login_required
def course_dashboard():
    if current_user.role == 'admin':
        return redirect(url_for('admin_dashboard'))

    # 1. Fetch the entire course structure, only published modules
    modules = Module.query.filter_by(is_published=True).order_by(Module.order.asc()).all()

    # 2. Get a map of the user's progress for efficient lookups
    user_progress_map = get_user_progress_map(current_user.id)
    
    # 3. Calculate progress for each module and the overall course progress
    module_progress_data = {}
    total_items_in_course = 0
    completed_items_in_course = 0

    for module in modules:
        # Calculate progress for this specific module
        progress_percent = calculate_module_progress(module, user_progress_map)
        module_progress_data[module.id] = progress_percent
        
        # Add to overall course totals. We need to query the item count here.
        # This is a bit more expensive but necessary for an accurate overall bar.
        module_item_count = ModuleItem.query.join(Submodule).filter(Submodule.module_id == module.id).count()
        total_items_in_course += module_item_count
        
        # Calculate completed items for this module based on the percentage
        completed_count_for_module = round(module_item_count * (progress_percent / 100))
        completed_items_in_course += completed_count_for_module

    # Calculate final overall course progress
    overall_progress = 0
    if total_items_in_course > 0:
        overall_progress = round((completed_items_in_course / total_items_in_course) * 100)

    # 4. Get a dictionary of certificates the user has earned, keyed by module_id
    user_certificates = {cert.module_id: cert for cert in current_user.certificates}

    return render_template(
        'course.html', 
        modules=modules,
        module_progress_data=module_progress_data,
        user_progress_map=user_progress_map,
        overall_progress=overall_progress,
        user_certificates=user_certificates
    )

@app.route("/logout")
def logout():
    logout_user()
    return redirect(url_for('index'))

# app.py



# app.py



@app.route("/labs")
@login_required
def lab_list():
    labs = Lab.query.order_by(Lab.id.asc()).all()
    # We can add progress tracking here later
    return render_template('lab_list.html', labs=labs)

@app.route("/certificates")
@login_required
def certificates_list():
    certificates = Certificate.query.filter_by(user_id=current_user.id).order_by(Certificate.completion_date.desc()).all()
    return render_template('certificate_list.html', certificates=certificates)

# ===================================
# 3.8 CONTEXT PROCESSOR
# ===================================
@app.context_processor
def inject_now():
    """Injects the 'now' variable (current time) into all templates."""
    return {'now': datetime.datetime.utcnow()}

@app.template_filter('markdown')
def markdown_filter(s):
    return markdown.markdown(s, extensions=['fenced_code', 'tables'])

# app.py

# ... (in the ROUTES section, after lab_list) ...

# 1. "Start Lab" Route - Redirects to the correct step
@app.route("/lab/<int:lab_id>/start")
@login_required
def start_lab(lab_id):
    lab = Lab.query.get_or_404(lab_id)
    
    # Find user's progress, or create it if it doesn't exist
    progress = LabProgress.query.filter_by(user_id=current_user.id, lab_id=lab.id).first()
    if not progress:
        progress = LabProgress(user_id=current_user.id, lab_id=lab.id, current_step_number=1)
        db.session.add(progress)
        db.session.commit()
        
    return redirect(url_for('lab_step_viewer', lab_id=lab.id, step_number=progress.current_step_number))
# app.py
# app.py -> ROUTES section

# This is the old lab_step_viewer. We will remove the completion logic from it.
@app.route("/lab/<int:lab_id>/step/<int:step_number>")
@login_required
def lab_step_viewer(lab_id, step_number):
    lab = Lab.query.get_or_404(lab_id)
    step = LabStep.query.filter_by(lab_id=lab.id, step_number=step_number).first_or_404()
    total_steps = len(lab.steps)
    return render_template('lab_viewer.html', lab=lab, step=step, total_steps=total_steps)

# app.py -> ROUTES section

@app.route("/lab/<int:lab_id>/complete")
@login_required
def lab_complete(lab_id):
    lab = Lab.query.get_or_404(lab_id)
    
    # --- NEW AND IMPROVED LOGIC ---
    progress = LabProgress.query.filter_by(user_id=current_user.id, lab_id=lab.id).first()
    
    # If progress exists, reset it to step 1 for the next time.
    if progress:
        progress.current_step_number = 1
        db.session.commit()
    # --- END OF NEW LOGIC ---
        
    # Render the completion page as before.
    return render_template('lab_complete.html', lab=lab)
    
# ===================================
# 4. ROUTES
# ===================================
# ... your routes start here ...# app.py

@app.route("/quiz/<int:quiz_id>")
@login_required
def quiz_viewer(quiz_id):
    # This route is now simpler. We just fetch the quiz object.
    # The relationships in our models will allow Jinja to access questions and options.
    quiz = Quiz.query.get_or_404(quiz_id)

    # Find the parent module and submodule
    module_item = ModuleItem.query.filter_by(content_type='quiz', content_id=quiz.id).first()
    module_id = None
    submodule_id = None
    if module_item:
        submodule_id = module_item.submodule_id
        if module_item.submodule:
            module_id = module_item.submodule.module_id

    # We still need the questions with answers for the review part later.
    questions_for_review = []
    for q in quiz.questions:
        options = [{'id': opt.id, 'text': opt.option_text, 'is_correct': opt.is_correct} for opt in q.options]
        questions_for_review.append({'id': q.id, 'text': q.question_text, 'options': options})
    questions_with_answers_json = json.dumps(questions_for_review)

    return render_template(
        'quiz_viewer.html', 
        quiz=quiz,
        questions_with_answers_json=questions_with_answers_json,
        module_id=module_id,
        submodule_id=submodule_id
    )

# app.py

@app.route("/dashboard")
@login_required
def dashboard():
    # This route is for non-admin users. If an admin lands here, send them to their own dashboard.
    if current_user.role == 'admin':
        return redirect(url_for('admin_dashboard'))

    # Calculate user statistics for their quiz performance
    attempts = QuizAttempt.query.filter_by(user_id=current_user.id).all()
    
    # Calculate stats based on UNIQUE quizzes the user has interacted with
    quizzes_taken = len(set(a.quiz_id for a in attempts))
    quizzes_passed = len(set(a.quiz_id for a in attempts if a.passed))
    
    pass_rate = 0
    # Avoid division by zero for new users
    if quizzes_taken > 0:
        pass_rate = round((quizzes_passed / quizzes_taken) * 100)
        
    # Get the 3 most recent attempts for the activity feed
    recent_attempts = QuizAttempt.query.filter_by(user_id=current_user.id).order_by(QuizAttempt.timestamp.desc()).limit(3).all()

    # Get all certificates for the user
    certificates = Certificate.query.filter_by(user_id=current_user.id).order_by(Certificate.completion_date.desc()).all()

    stats = {
        'quizzes_taken': quizzes_taken,
        'quizzes_passed': quizzes_passed,
        'pass_rate': pass_rate,
        'recent_attempts': recent_attempts
    }
    
    return render_template('dashboard.html', stats=stats, certificates=certificates)    
    
@app.route("/admin")
@login_required
def admin_dashboard():
    # Ensure the user has the 'admin' role.
    if current_user.role != 'admin':
        flash('You do not have permission to access this page.', 'danger')
        return redirect(url_for('dashboard'))

    return render_template('admin_dashboard.html')




# app.py

@app.route("/admin/module/<int:module_id>/manage")
@login_required
def admin_manage_module_content(module_id):
    if current_user.role != 'admin':
        flash('Permission denied.', 'danger')
        return redirect(url_for('dashboard'))
    
    module = Module.query.get_or_404(module_id)
    
    return render_template(
        'admin_module_detail.html', 
        item=module
    )

@app.route("/admin/submodule/<int:submodule_id>/manage")
@login_required
def admin_manage_submodule_content(submodule_id):
    if current_user.role != 'admin':
        flash('Permission denied.', 'danger')
        return redirect(url_for('dashboard'))
    
    submodule = Submodule.query.get_or_404(submodule_id)
    
    return render_template(
        'admin_module_detail.html', 
        item=submodule
    )

@app.route("/admin/quizzes", methods=['GET', 'POST'])
@login_required
def admin_quizzes():
    if current_user.role != 'admin':
        flash('You do not have permission to access this page.', 'danger')
        return redirect(url_for('dashboard'))

    if request.method == 'POST':
        category = request.form.get('category')
        
        # --- CORRECTED VALIDATION LOGIC ---
        
        # 1. Check if the file part exists in the request
        if 'quiz_file' not in request.files:
            flash('No file part in the request. This is likely a form error.', 'danger')
            return redirect(request.url)
        
        # 2. Now that we know it exists, get the file object
        file = request.files['quiz_file']
        
        # 3. Check if the user submitted an empty file part (no filename)
        if file.filename == '':
            flash('No file selected. Please choose a file to upload.', 'danger')
            return redirect(request.url)

        # --- END OF CORRECTION ---

        if not category or category not in QUIZ_CATEGORIES:
            flash('Invalid category selected.', 'danger')
            return redirect(request.url)
        
        if file and file.filename.endswith('.md'):
            filename = file.filename
            
            if Quiz.query.filter_by(filename=filename).first():
                flash(f"A quiz with the filename '{filename}' already exists.", 'danger')
                return redirect(request.url)

            markdown_text = file.stream.read().decode("utf-8")
            parsed_data = parse_quiz_markdown(markdown_text)

            if parsed_data is None:
                flash('Failed to parse the markdown file. Please check its format.', 'danger')
                return redirect(request.url)

            expected_count = QUIZ_CATEGORIES[category]['questions']
            actual_count = len(parsed_data)
            if actual_count != expected_count:
                flash(f"Validation Error: The selected category '{QUIZ_CATEGORIES[category]['name']}' requires exactly {expected_count} questions, but the file contained {actual_count}.", 'danger')
                return redirect(request.url)
            
            try:
                quiz_title = os.path.splitext(filename)[0].replace('_', ' ').title()
                selected_category_data = QUIZ_CATEGORIES[category]
                new_quiz = Quiz(title=quiz_title, filename=filename, category=selected_category_data['name'], passing_score=selected_category_data['passing_score'])
                db.session.add(new_quiz)
                
                for q_data in parsed_data:
                    new_question = Question(question_text=q_data['question_text'], quiz=new_quiz)
                    db.session.add(new_question)
                    for i, opt_text in enumerate(q_data['options']):
                        is_correct = (i == q_data['correct_index'])
                        new_option = Option(option_text=opt_text, is_correct=is_correct, question=new_question)
                        db.session.add(new_option)
                
                db.session.commit()
                flash(f"Successfully uploaded and created '{quiz_title}'.", 'success')
            except Exception as e:
                db.session.rollback()
                flash(f"A database error occurred: {e}", "danger")

            return redirect(url_for('admin_quizzes'))
        else:
            flash('Invalid file type. Please upload a .md file.', 'danger')
            return redirect(request.url)

    # Logic for GET request
    quizzes = Quiz.query.order_by(Quiz.id.desc()).all()
    return render_template('admin_quiz_management.html', quizzes=quizzes, categories=QUIZ_CATEGORIES)
    
# app.py

@app.route("/admin/labs", methods=['GET', 'POST'])
@login_required
def admin_labs():
    if current_user.role != 'admin':
        flash('You do not have permission to access this page.', 'danger')
        return redirect(url_for('dashboard'))

    # --- NEW: LOGIC TO HANDLE FILE UPLOAD ---
    if request.method == 'POST':
        if 'lab_file' not in request.files:
            flash('No file part in the request.', 'danger')
            return redirect(request.url)
        
        file = request.files['lab_file']
        
        if file.filename == '':
            flash('No file selected.', 'danger')
            return redirect(request.url)

        if file and file.filename.endswith('.md'):
            filename = file.filename
            
            # Check for duplicate filename
            if Lab.query.filter_by(filename=filename).first():
                flash(f"A lab with the filename '{filename}' already exists.", 'danger')
                return redirect(request.url)

            # Read and parse the file with our strict parser
            markdown_text = file.stream.read().decode("utf-8")
            parsed_data, error_message = parse_lab_markdown(markdown_text)

            # If the parser returns an error, flash it to the admin
            if error_message:
                flash(f"Upload Failed: {error_message}", 'danger')
                return redirect(request.url)
            
            # If parsing succeeds, add to the database
            try:
                lab_title = os.path.splitext(filename)[0].replace('_', ' ').title()
                new_lab = Lab(title=lab_title, filename=filename)
                db.session.add(new_lab)
                
                for step_data in parsed_data:
                    new_step = LabStep(
                        step_number=step_data['step_number'],
                        description_text=step_data['description_text'],
                        type_text=step_data['type_text'],
                        match_text=step_data['match_text'],
                        lab=new_lab
                    )
                    db.session.add(new_step)
                
                db.session.commit()
                flash(f"Successfully uploaded and created Lab: '{lab_title}'.", 'success')
            except Exception as e:
                db.session.rollback()
                flash(f"A database error occurred: {e}", "danger")

            return redirect(url_for('admin_labs'))
        else:
            flash('Invalid file type. Please upload a .md file.', 'danger')
            return redirect(request.url)

    # --- EXISTING LOGIC FOR GET REQUEST ---
    labs = Lab.query.order_by(Lab.id.desc()).all()
    return render_template('admin_lab_management.html', labs=labs)    
    
# app.py -> ROUTES section

@app.route("/admin/sessions")
@login_required
def admin_sessions():
    if current_user.role != 'admin':
        flash('You do not have permission to access this page.', 'danger')
        return redirect(url_for('dashboard'))
    
    sessions = PracticalSession.query.all()
    return render_template('admin_session_management.html', sessions=sessions)

@app.route("/admin/notes", methods=['GET', 'POST'])
@login_required
def admin_notes():
    if current_user.role != 'admin':
        flash('You do not have permission to access this page.', 'danger')
        return redirect(url_for('dashboard'))

    if request.method == 'POST':
        if 'note_file' not in request.files:
            flash('No file part in the request.', 'danger')
            return redirect(request.url)
        
        file = request.files['note_file']
        quiz_id = request.form.get('quiz_id')
        
        if file.filename == '':
            flash('No file selected.', 'danger')
            return redirect(request.url)

        if not quiz_id:
            flash('Please select a quiz to associate with the note.', 'danger')
            return redirect(request.url)

        selected_quiz = Quiz.query.get(quiz_id)
        if not selected_quiz:
            flash('Selected quiz not found.', 'danger')
            return redirect(request.url)

        # Check if the selected quiz is already associated with another note
        if Note.query.filter_by(quiz_id=quiz_id).first():
            flash('This quiz is already associated with another note. Please choose a different one.', 'danger')
            return redirect(request.url)

        if file and file.filename.endswith('.md'):
            filename = file.filename
            
            if Note.query.filter_by(filename=filename).first():
                flash(f"A note with the filename '{filename}' already exists.", 'danger')
                return redirect(request.url)

            markdown_text = file.stream.read().decode("utf-8")
            
            try:
                note_title = os.path.splitext(filename)[0].replace('_', ' ').title()
                new_note = Note(title=note_title, filename=filename, content=markdown_text, quiz_id=selected_quiz.id)
                
                db.session.add(new_note)
                db.session.commit()
                flash(f"Successfully uploaded and created Note: '{note_title}'.", 'success')
            except Exception as e:
                db.session.rollback()
                flash(f"A database error occurred: {e}", "danger")

            return redirect(url_for('admin_notes'))
        else:
            flash('Invalid file type. Please upload a .md file.', 'danger')
            return redirect(request.url)

    # For GET request: Fetch available quizzes
    # A quiz is available if it's a 'Quiz' category, has 3 questions, and is not already associated with a note.
    available_quizzes = Quiz.query.filter(
        Quiz.category == 'Quiz',
        ~Quiz.id.in_(db.session.query(Note.quiz_id))
    ).all()
    print(f"DEBUG: Available quizzes for notes: {available_quizzes}")

    notes = Note.query.order_by(Note.id.desc()).all()
    return render_template('admin_note_management.html', notes=notes, available_quizzes=available_quizzes)

# ===================================
# COURSE BUILDER ROUTES (ADMIN)
# ===================================

@app.route("/admin/course-builder", methods=['GET', 'POST'])
@login_required
def admin_course_builder():
    if current_user.role != 'admin':
        flash('You do not have permission to access this page.', 'danger')
        return redirect(url_for('dashboard'))

    # Handle Module Creation
    if request.method == 'POST':
        title = request.form.get('title')
        is_published = 'is_published' in request.form

        if not title:
            flash('Module title cannot be empty.', 'danger')
        else:
            # Determine the order for the new module
            last_module = Module.query.order_by(Module.order.desc()).first()
            new_order = last_module.order + 1 if last_module else 1
            
            new_module = Module(title=title, order=new_order, is_published=is_published)
            db.session.add(new_module)
            db.session.commit()
            flash(f"Module '{title}' created successfully.", 'success')
        return redirect(url_for('admin_course_builder'))

    # GET Request: Display all modules
    modules = Module.query.order_by(Module.order.asc()).all()
    user_progress_map = get_user_progress_map(current_user.id)
    module_progress_data = {}
    for module in modules:
        progress_percent = calculate_module_progress(module, user_progress_map)
        module_progress_data[module.id] = progress_percent

    return render_template('admin_course_management.html', modules=modules, module_progress_data=module_progress_data, user_progress_map=user_progress_map)

@app.route("/admin/module/<int:module_id>/delete", methods=['POST'])
@login_required
def delete_module(module_id):
    if current_user.role != 'admin':
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
    
    module_to_delete = Module.query.get_or_404(module_id)
    
    try:
        # Store the order of the module to be deleted
        deleted_module_order = module_to_delete.order

        # Temporarily set the order of the module to be deleted to a non-conflicting value
        # This effectively "frees up" its order slot before reordering others
        module_to_delete.order = -999999 # Use a large negative number
        db.session.flush() # Apply this change to the session

        # Delete the module
        db.session.delete(module_to_delete)
        # db.session.flush() # No need to flush here, deletion will happen on commit

        # Re-order subsequent modules (those whose order was greater than the deleted module's original order)
        # Iterate in ascending order to avoid conflicts when decrementing
        subsequent_modules = Module.query.filter(Module.order > deleted_module_order).order_by(Module.order.asc()).all()
        for mod in subsequent_modules:
            mod.order -= 1
            
        db.session.commit()
        return jsonify({'status': 'success', 'message': f'Module \'{module_to_delete.title}\' deleted.'})
    except Exception as e:
        db.session.rollback()
        return jsonify({'status': 'error', 'message': str(e)}), 500

@app.route("/admin/module/<int:module_id>/move/<direction>", methods=['POST'])
@login_required
def move_module(module_id, direction):
    if current_user.role != 'admin':
        return jsonify({'error': 'Permission denied'}), 403

    module_to_move = Module.query.get_or_404(module_id)
    
    if direction == 'up':
        swap_with = Module.query.filter(Module.order < module_to_move.order).order_by(Module.order.desc()).first()
    elif direction == 'down':
        swap_with = Module.query.filter(Module.order > module_to_move.order).order_by(Module.order.asc()).first()
    else:
        return redirect(url_for('admin_course_builder')) # Invalid direction

    if swap_with:
        try:
            original_order = module_to_move.order
            swap_with_order = swap_with.order

            module_to_move.order = swap_with_order
            swap_with.order = original_order
            db.session.commit()
            flash(f"Module '{module_to_move.title}' has been moved.", 'info')
        except Exception as e:
            db.session.rollback()
            flash(f"Error moving module: {e}", 'danger')
            print(f"Error moving module: {e}")
            return redirect(url_for('admin_course_builder'))
    return redirect(url_for('admin_course_builder'))    

# ===================================
# SUBMODULE MANAGEMENT ROUTES (ADMIN)
# ===================================

@app.route("/admin/module/<int:module_id>/manage", methods=['GET', 'POST'])
@login_required
def admin_manage_submodules(module_id):
    if current_user.role != 'admin':
        flash('You do not have permission to access this page.', 'danger')
        return redirect(url_for('dashboard'))
    
    module = Module.query.get_or_404(module_id)

    # Handle Submodule Creation
    if request.method == 'POST':
        title = request.form.get('title')
        if not title:
            flash('Submodule title cannot be empty.', 'danger')
        else:
            last_submodule = Submodule.query.filter_by(module_id=module.id).order_by(Submodule.order.desc()).first()
            new_order = last_submodule.order + 1 if last_submodule else 1
            
            new_submodule = Submodule(title=title, order=new_order, module_id=module.id)
            db.session.add(new_submodule)
            db.session.commit()
            flash(f"Submodule '{title}' created successfully.", 'success')
        return redirect(url_for('admin_manage_submodules', module_id=module.id))

    # GET Request: Display submodules for the given module
    # The relationship on the Module model automatically orders them.
    submodules = module.submodules.all()
    return render_template('admin_module_detail.html', item=module)

@app.route("/admin/submodule/<int:submodule_id>/edit", methods=['GET', 'POST'])
@login_required
def edit_submodule(submodule_id):
    if current_user.role != 'admin':
        flash('Permission denied.', 'danger')
        return redirect(url_for('dashboard'))
        
    submodule = Submodule.query.get_or_404(submodule_id)
    if request.method == 'POST':
        new_title = request.form.get('title')
        if not new_title:
            flash('Title cannot be empty.', 'danger')
            return redirect(url_for('edit_submodule', submodule_id=submodule.id))
        
        submodule.title = new_title
        db.session.commit()
        flash(f"Submodule title updated to '{new_title}'.", 'success')
        return redirect(url_for('admin_manage_submodules', module_id=submodule.module_id))

    return render_template('admin_edit_submodule.html', submodule=submodule)

@app.route("/admin/submodule/<int:submodule_id>/delete", methods=['POST'])
@login_required
def delete_submodule(submodule_id):
    if current_user.role != 'admin':
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
    
    submodule_to_delete = Submodule.query.get_or_404(submodule_id)
    module_id = submodule_to_delete.module_id
    
    try:
        # Re-order subsequent submodules within the same parent module
        subsequent_submodules = Submodule.query.filter(
            Submodule.module_id == module_id, 
            Submodule.order > submodule_to_delete.order
        ).all()
        for sub in subsequent_submodules:
            sub.order -= 1
            
        db.session.delete(submodule_to_delete)
        db.session.commit()
        return jsonify({'status': 'success', 'message': f'Submodule \'{submodule_to_delete.title}\' deleted.'})
    except Exception as e:
        db.session.rollback()
        return jsonify({'status': 'error', 'message': str(e)}), 500

@app.route("/admin/submodule/<int:submodule_id>/move/<direction>", methods=['POST'])
@login_required
def move_submodule(submodule_id, direction):
    if current_user.role != 'admin':
        return jsonify({'error': 'Permission denied'}), 403

    submodule_to_move = Submodule.query.get_or_404(submodule_id)
    module_id = submodule_to_move.module_id # Needed for query and redirect
    
    query = Submodule.query.filter_by(module_id=module_id)
    if direction == 'up':
        swap_with = query.filter(Submodule.order < submodule_to_move.order).order_by(Submodule.order.desc()).first()
    elif direction == 'down':
        swap_with = query.filter(Submodule.order > submodule_to_move.order).order_by(Submodule.order.asc()).first()
    else:
        return redirect(url_for('admin_manage_submodules', module_id=module_id))

    if swap_with:
        original_order = submodule_to_move.order
        swap_with_order = swap_with.order
        submodule_to_move.order = swap_with_order
        swap_with.order = original_order
        db.session.commit()
    return redirect(url_for('admin_manage_submodules', module_id=module_id))

# ===================================
# CONTENT ITEM MANAGEMENT ROUTES (ADMIN)
# ===================================

@app.route("/admin/submodule/<int:submodule_id>/content", methods=['GET'])
@login_required
def admin_manage_content(submodule_id):
    if current_user.role != 'admin':
        flash('Permission denied.', 'danger')
        return redirect(url_for('dashboard'))
        
    submodule = Submodule.query.get_or_404(submodule_id)
    # Find all content IDs that are already used in *any* module
    assigned_quiz_ids = {item.content_id for item in ModuleItem.query.filter_by(content_type='quiz').all()}
    assigned_lab_ids = {item.content_id for item in ModuleItem.query.filter_by(content_type='lab').all()}
    assigned_session_ids = {item.content_id for item in ModuleItem.query.filter_by(content_type='session').all()}
    assigned_note_ids = {item.content_id for item in ModuleItem.query.filter_by(content_type='note').all()}

    # Fetch available content that is NOT in the assigned lists
    available_quizzes = Quiz.query.filter(Quiz.id.notin_(assigned_quiz_ids)).order_by(Quiz.title).all()
    available_labs = Lab.query.filter(Lab.id.notin_(assigned_lab_ids)).order_by(Lab.title).all()
    available_sessions = PracticalSession.query.filter(PracticalSession.id.notin_(assigned_session_ids)).order_by(PracticalSession.title).all()
    available_notes = Note.query.filter(Note.id.notin_(assigned_note_ids)).order_by(Note.title).all()
    print(f"DEBUG: Available notes for submodule {submodule_id}: {available_notes}")
    
    # The submodule's items are fetched via the relationship and are already ordered
    items = submodule.items.all()
    print(f"DEBUG: In admin_manage_content for submodule {submodule_id}. Items found: {len(items)}")
    for item in items:
        print(f"DEBUG: ModuleItem ID: {item.id}, Type: {item.content_type}, Content ID: {item.content_id}, Content Object: {item.content_object}")

    return render_template(
        'admin_manage_content.html', 
        submodule=submodule, 
        items=items,
        available_quizzes=available_quizzes,
        available_labs=available_labs,
        available_sessions=available_sessions,
        available_notes=available_notes
    )

@app.route("/admin/item/<int:item_id>/unlink", methods=['POST'])
@login_required
def unlink_content_item(item_id):
    if current_user.role != 'admin':
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
    
    item_to_delete = ModuleItem.query.get_or_404(item_id)
    submodule_id = item_to_delete.submodule_id
    
    try:
        # Re-order subsequent items
        subsequent_items = ModuleItem.query.filter(
            ModuleItem.submodule_id == submodule_id, 
            ModuleItem.order > item_to_delete.order
        ).all()
        for item in subsequent_items:
            item.order -= 1
            
        db.session.delete(item_to_delete)
        db.session.commit()
        return jsonify({'status': 'success', 'message': 'Content item unlinked from submodule.'})
    except Exception as e:
        db.session.rollback()
        return jsonify({'status': 'error', 'message': str(e)}), 500

@app.route("/admin/content/<string:content_type>/<int:content_id>/delete", methods=['POST'])
@login_required
def delete_content(content_type, content_id):
    if current_user.role != 'admin':
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403

    try:
        if content_type == 'quiz':
            content_to_delete = Quiz.query.get_or_404(content_id)
        elif content_type == 'lab':
            content_to_delete = Lab.query.get_or_404(content_id)
        elif content_type == 'session':
            content_to_delete = PracticalSession.query.get_or_404(content_id)
        elif content_type == 'note':
            content_to_delete = Note.query.get_or_404(content_id)
        else:
            return jsonify({'status': 'error', 'message': 'Invalid content type'}), 400

        # Also delete all ModuleItems that point to this content
        ModuleItem.query.filter_by(content_type=content_type, content_id=content_id).delete()

        db.session.delete(content_to_delete)
        db.session.commit()
        return jsonify({'status': 'success', 'message': f'{content_type.capitalize()} deleted successfully.'})
    except Exception as e:
        db.session.rollback()
        return jsonify({'status': 'error', 'message': str(e)}), 500

@app.route("/admin/item/<int:item_id>/move/<direction>", methods=['POST'])
@login_required
def move_content_item(item_id, direction):
    if current_user.role != 'admin':
        return jsonify({'error': 'Permission denied'}), 403

    item_to_move = ModuleItem.query.get_or_404(item_id)
    submodule_id = item_to_move.submodule_id
    
    query = ModuleItem.query.filter_by(submodule_id=submodule_id)
    if direction == 'up':
        swap_with = query.filter(ModuleItem.order < item_to_move.order).order_by(ModuleItem.order.desc()).first()
    elif direction == 'down':
        swap_with = query.filter(ModuleItem.order > item_to_move.order).order_by(ModuleItem.order.asc()).first()
    else:
        return redirect(url_for('admin_manage_content', submodule_id=submodule_id))

    if swap_with:
        original_order = item_to_move.order
        item_to_move.order = swap_with.order
        swap_with.order = original_order
        db.session.commit()

    return redirect(url_for('admin_manage_content', submodule_id=submodule_id))

# ===================================
# NEW ADMIN DASHBOARD API ENDPOINTS
# ===================================

@app.route("/admin/module/create", methods=['POST'])
@login_required
def create_module():
    print("DEBUG: Entering create_module function.")
    print(f"DEBUG: Request method: {request.method}")
    print(f"DEBUG: Request headers: {request.headers}")
    print(f"DEBUG: Is JSON request: {request.is_json}")

    if current_user.role != 'admin':
        print(f"DEBUG: Permission denied for create_module. User ID: {current_user.id}, Role: {current_user.role}")
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
    print(f"DEBUG: User {current_user.id} with role {current_user.role} is authorized.")

    if not request.is_json:
        print("ERROR: Request is not JSON for create_module.")
        return jsonify({'status': 'error', 'message': 'Content-Type must be application/json'}), 400

    data = request.get_json()
    print(f"DEBUG: Received JSON data for create_module: {data}")
    title = data.get('title', '').strip()
    print(f"DEBUG: Extracted title: '{title}'")

    if not title:
        print("DEBUG: Title is empty for create_module.")
        return jsonify({'status': 'error', 'message': 'Title cannot be empty'}), 400
    
    try:
        print("DEBUG: Attempting to find last module for order calculation.")
        last_module = Module.query.order_by(Module.order.desc()).first()
        new_order = last_module.order + 1 if last_module else 1
        print(f"DEBUG: Calculated new order: {new_order}")
        
        new_module = Module(title=title, order=new_order, is_published=False)
        db.session.add(new_module)
        print(f"DEBUG: Added new module to session: {new_module}")
        db.session.commit()
        print(f"DEBUG: Module \"{title}\" created successfully with ID: {new_module.id} and committed to DB.")
        return jsonify({'status': 'success', 'message': 'Module created', 'module_id': new_module.id})
    except Exception as e:
        db.session.rollback()
        print(f"ERROR: Exception during create_module: {str(e)}")
        import traceback
        traceback.print_exc()
        return jsonify({'status': 'error', 'message': str(e)}), 500

@app.route("/admin/module/<int:module_id>/toggle-publish", methods=['POST'])
@login_required
def toggle_module_publish(module_id):
    if current_user.role != 'admin':
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
    
    module = Module.query.get_or_404(module_id)
    module.is_published = not module.is_published
    db.session.commit()
    
    return jsonify({'status': 'success', 'message': 'Publish status toggled', 'is_published': module.is_published})

@app.route("/admin/module/<int:module_id>/duplicate", methods=['POST'])
@login_required
def duplicate_module(module_id):
    if current_user.role != 'admin':
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
    
    original_module = Module.query.get_or_404(module_id)
    
    try:
        # Create new module
        last_module = Module.query.order_by(Module.order.desc()).first()
        new_order = last_module.order + 1 if last_module else 1
        
        new_module = Module(
            title=original_module.title + ' (Copy)',
            order=new_order,
            is_published=False
        )
        db.session.add(new_module)
        db.session.flush()
        
        # Duplicate submodules and items
        for submodule in original_module.submodules:
            new_submodule = Submodule(
                title=submodule.title,
                order=submodule.order,
                module_id=new_module.id
            )
            db.session.add(new_submodule)
            db.session.flush()
            
            for item in submodule.items:
                new_item = ModuleItem(
                    order=item.order,
                    submodule_id=new_submodule.id,
                    content_type=item.content_type,
                    content_id=item.content_id
                )
                db.session.add(new_item)
        
        db.session.commit()
        return jsonify({'status': 'success', 'message': 'Module duplicated'})
    except Exception as e:
        db.session.rollback()
        return jsonify({'status': 'error', 'message': str(e)}), 500

@app.route("/admin/submodule/create", methods=['POST'])
@login_required
def create_submodule():
    if current_user.role != 'admin':
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403

    data = request.get_json()
    title = data.get('title', '').strip()
    module_id = data.get('module_id')
    parent_id = data.get('parent_id')

    if not title:
        return jsonify({'status': 'error', 'message': 'Title is required'}), 400

    if not module_id and not parent_id:
        return jsonify({'status': 'error', 'message': 'Either module_id or parent_id is required'}), 400

    try:
        if parent_id:
            parent_submodule = Submodule.query.get_or_404(parent_id)
            last_submodule = Submodule.query.filter_by(parent_id=parent_id).order_by(Submodule.order.desc()).first()
            new_order = last_submodule.order + 1 if last_submodule else 1
            new_submodule = Submodule(title=title, order=new_order, parent_id=parent_id, module_id=parent_submodule.module_id)
        else:
            module = Module.query.get_or_404(module_id)
            last_submodule = Submodule.query.filter_by(module_id=module_id, parent_id=None).order_by(Submodule.order.desc()).first()
            new_order = last_submodule.order + 1 if last_submodule else 1
            new_submodule = Submodule(title=title, order=new_order, module_id=module_id)

        db.session.add(new_submodule)
        db.session.commit()

        return jsonify({'status': 'success', 'message': 'Submodule created', 'submodule_id': new_submodule.id})
    except Exception as e:
        db.session.rollback()
        import traceback
        traceback.print_exc()
        return jsonify({'status': 'error', 'message': str(e)}), 500

@app.route("/admin/submodule/<int:submodule_id>/duplicate", methods=['POST'])
@login_required
def duplicate_submodule(submodule_id):
    if current_user.role != 'admin':
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
    
    original_submodule = Submodule.query.get_or_404(submodule_id)
    
    try:
        last_submodule = Submodule.query.filter_by(module_id=original_submodule.module_id).order_by(Submodule.order.desc()).first()
        new_order = last_submodule.order + 1 if last_submodule else 1
        
        new_submodule = Submodule(
            title=original_submodule.title + ' (Copy)',
            order=new_order,
            module_id=original_submodule.module_id
        )
        db.session.add(new_submodule)
        db.session.flush()
        
        # Duplicate items
        for item in original_submodule.items:
            new_item = ModuleItem(
                order=item.order,
                submodule_id=new_submodule.id,
                content_type=item.content_type,
                content_id=item.content_id
            )
            db.session.add(new_item)
        
        db.session.commit()
        return jsonify({'status': 'success', 'message': 'Submodule duplicated'})
    except Exception as e:
        db.session.rollback()
        return jsonify({'status': 'error', 'message': str(e)}), 500

@app.route("/admin/item/create", methods=['POST'])
@login_required
def create_content_item():
    if current_user.role != 'admin':
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
    
    data = request.get_json()
    submodule_id = data.get('submodule_id')
    content_type = data.get('content_type')
    content_id = data.get('content_id')
    
    if not submodule_id:
        return jsonify({'status': 'error', 'message': 'Submodule ID is required'}), 400
    if not content_type:
        return jsonify({'status': 'error', 'message': 'Content type is required'}), 400
    if not content_id:
        return jsonify({'status': 'error', 'message': 'Content ID is required'}), 400

    try:
        submodule = Submodule.query.get(submodule_id)
        if not submodule:
            return jsonify({'status': 'error', 'message': 'Submodule not found'}), 404
        
        content_id = int(content_id) # Ensure content_id is an integer

        # Check for duplicate content item within the same submodule
        existing_item = ModuleItem.query.filter_by(
            submodule_id=submodule_id,
            content_type=content_type,
            content_id=content_id
        ).first()
        if existing_item:
            return jsonify({'status': 'error', 'message': 'This content item already exists in this submodule.'}), 409 # 409 Conflict

        last_item = ModuleItem.query.filter_by(submodule_id=submodule_id).order_by(ModuleItem.order.desc()).first()
        new_order = last_item.order + 1 if last_item else 1
        
        new_item = ModuleItem(
            order=new_order,
            submodule_id=submodule_id,
            content_type=content_type,
            content_id=content_id
        )
        db.session.add(new_item)
        db.session.commit()
        
        return jsonify({'status': 'success', 'message': 'Content item created', 'item_id': new_item.id})
    except ValueError:
        db.session.rollback()
        return jsonify({'status': 'error', 'message': 'Content ID must be an integer.'}), 400
    except Exception as e:
        db.session.rollback()
        import traceback
        traceback.print_exc()
        return jsonify({'status': 'error', 'message': str(e)}), 500

@app.route("/admin/content/list/<content_type>")
@login_required
def list_content_by_type(content_type):
    if current_user.role != 'admin':
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
    
    try:
        # Find all content IDs that are already used in *any* module
        assigned_quiz_ids = {item.content_id for item in ModuleItem.query.filter_by(content_type='quiz').all()}
        assigned_lab_ids = {item.content_id for item in ModuleItem.query.filter_by(content_type='lab').all()}
        assigned_session_ids = {item.content_id for item in ModuleItem.query.filter_by(content_type='session').all()}
        assigned_note_ids = {item.content_id for item in ModuleItem.query.filter_by(content_type='note').all()}

        if content_type == 'quiz':
            items = Quiz.query.filter(Quiz.id.notin_(assigned_quiz_ids)).order_by(Quiz.title).all()
        elif content_type == 'lab':
            items = Lab.query.filter(Lab.id.notin_(assigned_lab_ids)).order_by(Lab.title).all()
        elif content_type == 'session':
            items = PracticalSession.query.filter(PracticalSession.id.notin_(assigned_session_ids)).order_by(PracticalSession.title).all()
        elif content_type == 'note':
            items = Note.query.filter(Note.id.notin_(assigned_note_ids)).order_by(Note.title).all()
        else:
            return jsonify({'status': 'error', 'message': 'Invalid content type'}), 400
        
        return jsonify({
            'status': 'success',
            'items': [{'id': item.id, 'title': item.title} for item in items]
        })
    except Exception as e:
        return jsonify({'status': 'error', 'message': str(e)}), 500


@app.route("/admin/modules/reorder", methods=['POST'])
@login_required
def reorder_modules():
    if current_user.role != 'admin':
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
    
    new_order_ids = request.json.get('new_order')
    if not new_order_ids:
        return jsonify({'status': 'error', 'message': 'No new order provided'}), 400

    try:
        modules = Module.query.filter(Module.id.in_(new_order_ids)).all()
        module_map = {str(module.id): module for module in modules}

        for index, module_id in enumerate(new_order_ids):
            if module_id in module_map:
                module_map[module_id].order = index + 1
        db.session.commit()
        return jsonify({'status': 'success', 'message': 'Module order updated.'})
    except Exception as e:
        db.session.rollback()
        return jsonify({'status': 'error', 'message': str(e)}), 500

@app.route("/admin/submodules/reorder", methods=['POST'])
@login_required
def reorder_submodules():
    if current_user.role != 'admin':
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403

    new_order_ids = request.json.get('new_order')
    module_id = request.json.get('module_id')
    parent_id = request.json.get('parent_id')

    if not new_order_ids:
        return jsonify({'status': 'error', 'message': 'No new order provided'}), 400

    if not module_id and not parent_id:
        return jsonify({'status': 'error', 'message': 'Either module_id or parent_id is required'}), 400

    try:
        if parent_id:
            submodules = Submodule.query.filter(
                Submodule.parent_id == parent_id,
                Submodule.id.in_(new_order_ids)
            ).all()
        else:
            submodules = Submodule.query.filter(
                Submodule.module_id == module_id,
                Submodule.id.in_(new_order_ids)
            ).all()

        submodule_map = {str(submodule.id): submodule for submodule in submodules}

        for index, submodule_id in enumerate(new_order_ids):
            if submodule_id in submodule_map:
                submodule_map[submodule_id].order = index + 1
        db.session.commit()
        return jsonify({'status': 'success', 'message': 'Submodule order updated.'})
    except Exception as e:
        db.session.rollback()
        return jsonify({'status': 'error', 'message': str(e)}), 500

@app.route("/admin/items/reorder", methods=['POST'])
@login_required
def reorder_content_items():
    if current_user.role != 'admin':
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
    
    new_order_ids = request.json.get('new_order')
    item_parent_id = request.json.get('parent_id') # Assuming parent_id is sent to scope the reorder
    
    if not new_order_ids or not item_parent_id:
        return jsonify({'status': 'error', 'message': 'Missing new order or parent ID'}), 400

    try:
        items = ModuleItem.query.filter(
            ModuleItem.submodule_id == item_parent_id,
            ModuleItem.id.in_(new_order_ids)
        ).all()
        item_map = {str(item.id): item for item in items}

        for index, item_id in enumerate(new_order_ids):
            if item_id in item_map:
                item_map[item_id].order = index + 1
        db.session.commit()
        return jsonify({'status': 'success', 'message': 'Content item order updated.'})
    except Exception as e:
        db.session.rollback()
        return jsonify({'status': 'error', 'message': str(e)}), 500

@app.route("/admin/module/<int:module_id>/rename", methods=['POST'])
@login_required
def rename_module(module_id):
    if current_user.role != 'admin':
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
    
    module = Module.query.get_or_404(module_id)
    data = request.get_json()
    new_title = data.get('new_title')

    if not new_title:
        return jsonify({'status': 'error', 'message': 'New title cannot be empty.'}), 400
    
    module.title = new_title
    db.session.commit()
    return jsonify({'status': 'success', 'message': 'Module renamed successfully.'})

@app.route("/admin/submodule/<int:submodule_id>/rename", methods=['POST'])
@login_required
def rename_submodule(submodule_id):
    if current_user.role != 'admin':
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
    
    submodule = Submodule.query.get_or_404(submodule_id)
    data = request.get_json()
    new_title = data.get('new_title')

    if not new_title:
        return jsonify({'status': 'error', 'message': 'New title cannot be empty.'}), 400
    
    submodule.title = new_title
    db.session.commit()
    return jsonify({'status': 'success', 'message': 'Submodule renamed successfully.'})

@app.route("/admin/item/<int:item_id>/rename", methods=['POST'])
@login_required
def rename_content_item(item_id):
    if current_user.role != 'admin':
        return jsonify({'status': 'error', 'message': 'Permission denied'}), 403
    
    item = ModuleItem.query.get_or_404(item_id)
    data = request.get_json()
    new_title = data.get('new_title')

    if not new_title:
        return jsonify({'status': 'error', 'message': 'New title cannot be empty.'}), 400
    
    # This is a bit tricky as content_object is a property.
    # We need to get the actual object (Quiz, Lab, PracticalSession) and update its title.
    content_object = item.content_object
    if content_object:
        content_object.title = new_title
        db.session.commit()
        return jsonify({'status': 'success', 'message': 'Content item renamed successfully.'})
    else:
        return jsonify({'status': 'error', 'message': 'Content object not found.'}), 404



# ===================================
# 5. CUSTOM CLI COMMANDS
# ===================================

@app.cli.command("create-admin")
def create_admin():
    """Creates the admin user."""
    print("Creating admin user...")
    try:
        if User.query.filter_by(username='admin').first():
            print("Admin user already exists.")
            return
        hashed_password = bcrypt.generate_password_hash('123456').decode('utf-8')
        admin_user = User(username='admin', email='admin@example.com', password_hash=hashed_password, role='admin')
        db.session.add(admin_user)
        db.session.commit()
        print("Admin user created successfully.")
    except Exception as e:
        print("Error creating admin user:", str(e))
        db.session.rollback()

# ===================================
# 6. CUSTOM CLI COMMANDS
# ===================================

# ... (your existing create-admin and process-quiz commands) ...

# --- ADD THE NEW LAB COMMAND BELOW ---

@app.cli.command("process-lab")
@click.argument("filepath")
def process_lab(filepath):
    """Processes a markdown lab file and adds it to the database."""
    print(f"--> Attempting to process lab file: {filepath}")

    # 1. Read the file content
    try:
        with open(filepath, 'r') as f:
            content = f.read()
    except FileNotFoundError:
        print(f"Error: File not found at '{filepath}'")
        return

    # 2. Parse the content using our strict, whitespace-aware parser
    parsed_data, error_message = parse_lab_markdown(content)
    
    # Check if the parser returned an error
    if error_message:
        print(f"VALIDATION FAILED: {error_message}")
        print("Aborting. No changes were made to the database.")
        return

    # If we get here, parsing was successful.
    print(f"--> Validation successful. Found {len(parsed_data)} steps.")
    
    # 3. Prepare to add to the database
    filename = os.path.basename(filepath)
    lab_title = os.path.splitext(filename)[0].replace('_', ' ').title()

    # Check for duplicate filename to prevent errors
    if Lab.query.filter_by(filename=filename).first():
        print(f"Error: A lab with the filename '{filename}' already exists. Aborting.")
        return
    
    try:
        # Create the parent Lab object
        new_lab = Lab(title=lab_title, filename=filename)
        db.session.add(new_lab)
        print(f"--> Creating Lab: '{lab_title}'")

        # Loop through the parsed data and create all associated LabStep objects
        for step_data in parsed_data:
            new_step = LabStep(
                step_number=step_data['step_number'],
                description_text=step_data['description_text'],
                type_text=step_data['type_text'],
                match_text=step_data['match_text'],
                lab=new_lab  # Associate this step with the parent lab
            )
            db.session.add(new_step)
        
        # Commit the entire transaction to the database
        db.session.commit()
        print(f"SUCCESS! Lab '{lab_title}' has been added to the database.")

    except Exception as e:
        # If any database error occurs, roll back all changes
        db.session.rollback()
        print(f"DATABASE ERROR: {e}")
        print("Transaction has been rolled back. No changes were saved.")

@app.cli.command("promote")
@click.argument("username")
def promote(username):
    """Promote an existing user to admin."""
    from app import db, User
    user = User.query.filter_by(username=username).first()
    if not user:
        print("User not found.")
        return
    user.role = "admin"
    db.session.commit()
    print(f"{username} promoted to admin.")


# ===================================
# 6. APP EXECUTION
# ===================================

if __name__ == '__main__':
    with app.app_context():
        create_admin()
    app.run(debug=True)
