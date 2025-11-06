import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';
import LanguageDetector from 'i18next-browser-languagedetector';
import Backend from 'i18next-http-backend';

i18n
  .use(Backend)
  .use(LanguageDetector)
  .use(initReactI18next)
  .init({
    fallbackLng: 'en',
    debug: process.env.NODE_ENV === 'development',
    
    interpolation: {
      escapeValue: false, // not needed for react as it escapes by default
    },
    
    supportedLngs: ['en', 'am', 'ti', 'or'], // English, Amharic, Tigrinya, Oromia
    
    backend: {
      loadPath: '/locales/{{lng}}/translation.json',
    },
    
    ns: ['translation'],
    defaultNS: 'translation',
    
    react: {
      useSuspense: true,
    },

    // Use localStorage for language detection first
    detection: {
      order: ['localStorage', 'navigator'],
      lookupLocalStorage: 'language',
      caches: ['localStorage']
    }
  });

export default i18n;