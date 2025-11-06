// Grade detector to update navigation based on referrer
document.addEventListener('DOMContentLoaded', function() {
    // Get the referrer URL
    const referrer = document.referrer;
    
    // Initialize with a default grade (in case we can't detect)
    let userGrade = 'ተማሪ';
    let gradeFolder = 'grade9s';
    
    // Check which grade the user came from
    if (referrer.includes('grade9s')) {
        userGrade = 'የ9ኛ ክፍል ተማሪ';
        gradeFolder = 'grade9s';
    } else if (referrer.includes('grade10s')) {
        userGrade = 'የ10ኛ ክፍል ተማሪ';
        gradeFolder = 'grade10s';
    } else if (referrer.includes('grade11s')) {
        userGrade = 'የ11ኛ ክፍል ተማሪ';
        gradeFolder = 'grade11s';
    } else if (referrer.includes('grade12s')) {
        userGrade = 'የ12ኛ ክፍል ተማሪ';
        gradeFolder = 'grade12s';
    }
    
    // Update the user grade in the sidebar
    const userRoleElement = document.querySelector('.user-role');
    if (userRoleElement) {
        userRoleElement.textContent = userGrade;
    }
    
    // Update navigation links
    const dashboardLink = document.querySelector('a[href*="dashboard.html"]');
    const learnLink = document.querySelector('a[href*="learn-cource.html"]');
    const workLink = document.querySelector('a[href*="work-assignment.html"]');
    const quizLink = document.querySelector('a[href*="take-quiz.html"]');
    const chatLink = document.querySelector('a[href*="chat.html"]');
    
    if (dashboardLink) dashboardLink.href = `../${gradeFolder}/dashboard.html`;
    if (learnLink) learnLink.href = `../${gradeFolder}/learn-cource.html`;
    if (workLink) workLink.href = `../${gradeFolder}/work-assignment.html`;
    if (quizLink) quizLink.href = `../${gradeFolder}/take-quiz.html`;
    if (chatLink) chatLink.href = `../${gradeFolder}/chat.html`;
    
    // Add a grade selector at the top of the content
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        const header = mainContent.querySelector('.header');
        if (header) {
            const gradeSelector = document.createElement('div');
            gradeSelector.className = 'grade-selector';
            gradeSelector.innerHTML = `
                <h2>ወደ ክፍሎች ይመለሱ</h2>
                <div class="grade-links">
                    <a href="../grade9s/dashboard.html" class="grade-link ${gradeFolder === 'grade9s' ? 'active' : ''}">የ9ኛ ክፍል</a>
                    <a href="../grade10s/dashboard.html" class="grade-link ${gradeFolder === 'grade10s' ? 'active' : ''}">የ10ኛ ክፍል</a>
                    <a href="../grade11s/dashboard.html" class="grade-link ${gradeFolder === 'grade11s' ? 'active' : ''}">የ11ኛ ክፍል</a>
                    <a href="../grade12s/dashboard.html" class="grade-link ${gradeFolder === 'grade12s' ? 'active' : ''}">የ12ኛ ክፍል</a>
                </div>
            `;
            
            // Add styles for the grade selector
            const style = document.createElement('style');
            style.textContent = `
                .grade-selector {
                    margin: 20px 0;
                    padding: 15px;
                    background-color: white;
                    border-radius: 10px;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                }
                
                .grade-selector h2 {
                    font-size: 16px;
                    margin-bottom: 10px;
                    color: #666;
                }
                
                .grade-links {
                    display: flex;
                    gap: 10px;
                    flex-wrap: wrap;
                }
                
                .grade-link {
                    padding: 8px 15px;
                    background-color: #f1f3f4;
                    color: #333;
                    border-radius: 5px;
                    text-decoration: none;
                    font-weight: 500;
                    transition: all 0.3s;
                }
                
                .grade-link:hover {
                    background-color: #e3e6e8;
                }
                
                .grade-link.active {
                    background-color: var(--primary-color, #4285f4);
                    color: white;
                }
            `;
            document.head.appendChild(style);
            
            mainContent.insertBefore(gradeSelector, header.nextSibling);
        }
    }
}); 