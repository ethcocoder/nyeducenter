const fs = require('fs-extra');
const path = require('path');

// Paths
const backendLocalesPath = path.join(__dirname, '../../backend/locales');
const frontendLocalesPath = path.join(__dirname, 'locales');

// Ensure locales directory exists
if (!fs.existsSync(frontendLocalesPath)) {
  fs.mkdirSync(frontendLocalesPath, { recursive: true });
}

// Supported languages
const languages = ['en', 'am', 'ti', 'or'];

// Copy each language file
languages.forEach(lang => {
  // Source file in backend
  const sourceFile = path.join(backendLocalesPath, `${lang}.json`);
  
  // Create directory for this language
  const targetDir = path.join(frontendLocalesPath, lang);
  fs.mkdirSync(targetDir, { recursive: true });
  
  // Target file in frontend
  const targetFile = path.join(targetDir, 'translation.json');
  
  // Copy the file
  try {
    if (fs.existsSync(sourceFile)) {
      fs.copyFileSync(sourceFile, targetFile);
      console.log(`Successfully copied ${lang} language file to frontend`);
    } else {
      console.error(`Source file for language ${lang} not found at ${sourceFile}`);
    }
  } catch (error) {
    console.error(`Error copying ${lang} language file:`, error);
  }
});

console.log('All language files copied successfully!'); 