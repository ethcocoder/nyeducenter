function showSystemSettings() {
    // Implement logic to show system settings modal
    console.log('Show System Settings Modal');
    // Example: Fetch current settings and populate a form
    loadSettings();
    // Assuming there's a modal with id 'systemSettingsModal'
    // var systemSettingsModal = new bootstrap.Modal(document.getElementById('systemSettingsModal'));
    // systemSettingsModal.show();
}

function loadSettings() {
    fetch('../../api/admin-get-settings.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.settings) {
                // Populate form fields with settings data
                console.log('System Settings:', data.settings);
                // Example: document.getElementById('setting1').value = data.settings.setting1;
            } else {
                console.error('API returned an error or no settings:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading system settings:', error);
        });
}

// Add event listeners for saving settings if applicable
// document.addEventListener('DOMContentLoaded', () => {
//     const saveSettingsBtn = document.getElementById('saveSystemSettingsBtn');
//     if (saveSettingsBtn) {
//         saveSettingsBtn.addEventListener('click', () => {
//             // Implement save settings logic
//             console.log('Saving system settings');
//         });
//     }
// });