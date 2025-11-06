/**
 * Form Validator Implementation for Course Creation Page
 * This file shows how to integrate the FormValidator with the course creation page
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get the course form
    const courseForm = document.getElementById('course-form');
    
    if (!courseForm) return; // Exit if form not found
    
    // Initialize the validator
    const validator = new FormValidator(courseForm, {
        validateOnInput: true,
        validateOnBlur: true,
        validateOnSubmit: true,
        errorClass: 'is-invalid',
        successClass: 'is-valid',
        errorMessageClass: 'error-message'
    });
    
    // Add validation rules for course title
    validator.addRule('course-title', FormValidator.rules.required, null, 'እባክዎ የኮርስ ርዕስ ያስገቡ');
    validator.addRule('course-title', FormValidator.rules.minLength, 5, 'የኮርስ ርዕስ ቢያንስ 5 ቁምፊዎች መሆን አለበት');
    validator.addRule('course-title', FormValidator.rules.maxLength, 100, 'የኮርስ ርዕስ ከ100 ቁምፊዎች መብለጥ የለበትም');
    
    // Add validation rules for course subject
    validator.addRule('course-subject', FormValidator.rules.required, null, 'እባክዎ የትምህርት አይነት ይምረጡ');
    
    // Add validation rules for course description
    validator.addRule('course-description', FormValidator.rules.required, null, 'እባክዎ የኮርስ መግለጫ ያስገቡ');
    validator.addRule('course-description', FormValidator.rules.minLength, 20, 'የኮርስ መግለጫ ቢያንስ 20 ቁምፊዎች መሆን አለበት');
    
    // Add validation rules for course class
    validator.addRule('course-class', FormValidator.rules.required, null, 'እባክዎ ክፍል ይምረጡ');
    
    // Custom validation rule for course image
    validator.addRule('course-image', function(value, field) {
        // If no image is required, return true
        if (!field.required) return true;
        
        // If there's no file input, check if we already have an image preview
        if (!field.files || field.files.length === 0) {
            return document.getElementById('preview-img').src !== '';
        }
        
        return field.files.length > 0;
    }, null, 'እባክዎ የኮርስ ምስል ይጫኑ');
    
    // Custom validation for file size and type
    validator.addRule('course-image', function(value, field) {
        if (!field.files || field.files.length === 0) return true;
        
        const file = field.files[0];
        const maxSize = 2 * 1024 * 1024; // 2MB
        
        if (file.size > maxSize) {
            return false;
        }
        
        return true;
    }, null, 'የምስል መጠን ከ2MB መብለጥ የለበትም');
    
    validator.addRule('course-image', function(value, field) {
        if (!field.files || field.files.length === 0) return true;
        
        const file = field.files[0];
        const allowedTypes = ['image/jpeg', 'image/png'];
        
        if (!allowedTypes.includes(file.type)) {
            return false;
        }
        
        return true;
    }, null, 'የተፈቀደው የምስል አይነት JPG ወይም PNG ብቻ ነው');
    
    // Custom validation for YouTube URL
    validator.addRule('video-url', function(value) {
        if (!value.trim()) return true; // Not required
        
        // YouTube URL regex
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
        const match = value.match(regExp);
        
        return match && match[2].length === 11;
    }, null, 'እባክዎ ትክክለኛ የYouTube URL ያስገቡ');
    
    // Handle form submission
    const saveDraftBtn = document.getElementById('save-draft-btn');
    const publishBtn = document.getElementById('publish-btn');
    
    saveDraftBtn.addEventListener('click', function() {
        // For draft, we only need minimal validation
        const titleField = document.getElementById('course-title');
        if (!titleField.value.trim()) {
            validator.validateField(titleField);
            return;
        }
        
        // Set status to draft
        document.getElementById('course-status').value = 'draft';
        
        // Process the form
        processCourseForm('draft');
    });
    
    courseForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Set status to published for submission
        document.getElementById('course-status').value = 'published';
        
        // Validate all fields
        if (validator.validateAll()) {
            processCourseForm('published');
        }
    });
    
    function processCourseForm(status) {
        // Get form data
        const formData = new FormData(courseForm);
        formData.append('status', status);
        
        // Add course image if present
        const courseImageInput = document.getElementById('course-image');
        if (courseImageInput.files.length > 0) {
            formData.append('courseImage', courseImageInput.files[0]);
        }
        
        // Get materials
        const materials = [];
        document.querySelectorAll('.material-item').forEach(function(item) {
            const materialId = item.dataset.id;
            const materialTitle = item.querySelector('.material-title').textContent;
            
            materials.push({
                id: materialId,
                title: materialTitle
                // Add additional material details as needed
            });
        });
        
        formData.append('materials', JSON.stringify(materials));
        
        // Show loading state
        const submitBtn = status === 'draft' ? saveDraftBtn : publishBtn;
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (status === 'draft' ? 'እየቀመጠ...' : 'እየላከ...');
        submitBtn.disabled = true;
        
        // Simulate API call (replace with actual implementation)
        setTimeout(function() {
            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            
            // Show success message
            alert(status === 'draft' 
                ? 'ኮርሱ እንደ ረቂቅ ተቀምጧል።' 
                : 'ኮርሱ በተሳካ ሁኔታ ተፈጥሯል እና ተልኳል።');
            
            // Redirect if published
            if (status === 'published') {
                // window.location.href = 'send-course.html';
            }
        }, 1500);
        
        /*
        // Actual API implementation would look like:
        api.upload('/api/courses', formData)
            .then(response => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                alert(status === 'draft' 
                    ? 'ኮርሱ እንደ ረቂቅ ተቀምጧል።' 
                    : 'ኮርሱ በተሳካ ሁኔታ ተፈጥሯል እና ተልኳል።');
                
                if (status === 'published') {
                    window.location.href = 'send-course.html';
                }
            })
            .catch(error => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                alert('ስህተት ተከስቷል: ' + error.message);
            });
        */
    }
    
    // Display error function (optional, FormValidator handles this)
    function showError(message) {
        const errorModal = document.getElementById('errorModal');
        const errorMessage = document.getElementById('errorMessage');
        
        if (errorModal && errorMessage) {
            errorMessage.textContent = message;
            errorModal.style.display = 'block';
            
            // Close modal when clicking the close button or outside the modal
            const closeButtons = errorModal.querySelectorAll('.close-modal');
            closeButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    errorModal.style.display = 'none';
                });
            });
            
            // Close when clicking outside
            window.addEventListener('click', function(e) {
                if (e.target === errorModal) {
                    errorModal.style.display = 'none';
                }
            });
        } else {
            // Fallback to alert if modal not found
            alert(message);
        }
    }
}); 