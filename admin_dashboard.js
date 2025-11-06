document.addEventListener('DOMContentLoaded', () => {
    const dashboardSection = document.getElementById('dashboard-section');
    const manageQuestionnairesLink = document.getElementById('manage-questionnaires');
    const manageResponsesLink = document.getElementById('manage-responses');
    const exportReportsLink = document.getElementById('export-reports');
    const manageSubscriptionsLink = document.getElementById('manage-subscriptions');

    const loadContent = (title, content) => {
        dashboardSection.innerHTML = `
            <h2>${title}</h2>
            <p>${content}</p>
        `;
    };

    manageQuestionnairesLink.addEventListener('click', (e) => {
        e.preventDefault();
        loadContent('Manage Questionnaires', 'Here you can create, edit, and delete questionnaires.');
    });

    manageResponsesLink.addEventListener('click', (e) => {
        e.preventDefault();
        loadContent('Manage Responses', 'View and manage user responses to questionnaires.');
    });

    exportReportsLink.addEventListener('click', (e) => {
        e.preventDefault();
        loadContent('Export & Reports', 'Generate and export reports in various formats (e.g., graphs, PDF).');
    });

    manageSubscriptionsLink.addEventListener('click', (e) => {
        e.preventDefault();
        loadContent('Manage Subscriptions', 'Accept and manage user subscriptions from the home page.');
    });

    document.addEventListener('DOMContentLoaded', function() {
        fetch('generate_training_report.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const labels = data.analysis.map(item => item.question_text);
                    const counts = data.analysis.map(item => Object.values(item.summary).reduce((a, b) => a + b, 0));

                    const ctx = document.getElementById('trainingChart').getContext('2d');
                    const trainingChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Number of Responses',
                                data: counts,
                                backgroundColor: [
                                    'rgba(30, 64, 175, 0.7)',
                                    'rgba(59, 130, 246, 0.7)',
                                    'rgba(245, 158, 11, 0.7)',
                                    'rgba(16, 185, 129, 0.7)',
                                    'rgba(30, 64, 175, 0.7)',
                                    'rgba(59, 130, 246, 0.7)',
                                    'rgba(245, 158, 11, 0.7)',
                                    'rgba(16, 185, 129, 0.7)'
                                ],
                                borderColor: [
                                    'rgba(30, 64, 175, 1)',
                                    'rgba(59, 130, 246, 1)',
                                    'rgba(245, 158, 11, 1)',
                                    'rgba(16, 185, 129, 1)',
                                    'rgba(30, 64, 175, 1)',
                                    'rgba(59, 130, 246, 1)',
                                    'rgba(245, 158, 11, 1)',
                                    'rgba(16, 185, 129, 1)'
                                ],
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 10
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });
                } else {
                    console.error('Error fetching training data:', data.message);
                }
            })
            .catch(error => console.error('Error fetching training data:', error));

        const ctx = document.getElementById('trainingChart').getContext('2d');
        const trainingChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Project Management', 'Financial Planning', 'Leadership Skills', 'Digital Skills', 'Team Collaboration', 'Problem Solving', 'Time Management', 'Communication'],
                datasets: [{
                    label: 'Number of Requests',
                    data: [45, 38, 52, 67, 29, 41, 33, 58],
                    backgroundColor: [
                        'rgba(30, 64, 175, 0.7)',
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(30, 64, 175, 0.7)',
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(245, 158, 11, 0.7)',
                        'rgba(16, 185, 129, 0.7)'
                    ],
                    borderColor: [
                        'rgba(30, 64, 175, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(30, 64, 175, 1)',
                        'rgba(59, 130, 246, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(16, 185, 129, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 10
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    
        // Button click handlers (mock functionality)
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function() {
                const buttonText = this.textContent;
                if (buttonText.includes('Export to PDF')) {
                    alert('PDF report generated successfully!');
                } else if (buttonText.includes('Generate Report')) {
                    alert('Detailed report generated with charts and analysis!');
                } else if (buttonText.includes('Export Data')) {
                    alert('Data exported to CSV format!');
                } else if (buttonText.includes('Edit Questionnaire')) {
                    alert('Redirecting to questionnaire management...');
                } else if (buttonText.includes('View Responses')) {
                    alert('Loading all questionnaire responses...');
                } else if (buttonText.includes('Manage Subscriptions')) {
                    alert('Managing newsletter subscriptions...');
                } else if (buttonText.includes('Clean Database')) {
                    if (confirm('Are you sure you want to clean the database? This action cannot be undone.')) {
                        alert('Database cleaned successfully!');
                    }
                }
            });
        });
    
        // Function to load content dynamically
        async function loadContent(url, targetElementId) {
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const content = await response.text();
                document.getElementById(targetElementId).innerHTML = content;
            } catch (error) {
                console.error('Error loading content:', error);
                document.getElementById(targetElementId).innerHTML = '<p>Error loading content.</p>';
            }
        }

        // Navigation active state and content loading
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', function(event) {
                event.preventDefault();

                document.querySelectorAll('.nav-links a').forEach(l => l.classList.remove('active'));
                this.classList.add('active');

                const targetId = this.getAttribute('href').substring(1);
                document.querySelectorAll('.content-section').forEach(section => {
                    section.style.display = 'none';
                });

                if (targetId === 'dashboard') {
                    document.getElementById('dashboard').style.display = 'block';
                } else if (targetId === 'questionnaire') {
                    document.getElementById('questionnaire-management').style.display = 'block';
                    loadContent('questionnaire_management.html', 'questionnaire-management');
                } else if (targetId === 'responses') {
                    document.getElementById('response-management').style.display = 'block';
                    loadContent('response_management.html', 'response-management');
                } else if (targetId === 'reports') {
                    document.getElementById('reports-export').style.display = 'block';
                    loadContent('reports_export.html', 'reports-export');
                } else if (targetId === 'subscriptions') {
                    document.getElementById('subscriptions-management').style.display = 'block';
                    loadContent('subscriptions_management.html', 'subscriptions-management');
                }
            });
        });

        // Initial content load for dashboard
        document.getElementById('dashboard').style.display = 'block';
    });
});