import { createTheme } from '@mui/material/styles';

export default createTheme({
  palette: {
    primary: {
      main: '#4e55af', // Rich purple-blue
      light: '#7c83e2',
      dark: '#2a307e',
      contrastText: '#ffffff',
    },
    secondary: {
      main: '#ff7043', // Vibrant orange
      light: '#ff9a76',
      dark: '#c63f17',
      contrastText: '#ffffff',
    },
    background: {
      default: '#f7f9fc',
      paper: '#ffffff',
    },
    text: {
      primary: '#333333',
      secondary: '#6b7280',
    },
    error: {
      main: '#f44336',
    },
    success: {
      main: '#4caf50',
    },
    info: {
      main: '#2196f3',
    },
    warning: {
      main: '#ff9800',
    },
  },
  typography: {
    fontFamily: "'Poppins', 'Roboto', 'Helvetica', 'Arial', sans-serif",
    h1: {
      fontWeight: 700,
    },
    h2: {
      fontWeight: 600,
    },
    h3: {
      fontWeight: 600,
    },
    button: {
      fontWeight: 600,
      textTransform: 'none',
    },
  },
  shape: {
    borderRadius: 10,
  },
  shadows: [
    'none',
    '0px 2px 4px rgba(0, 0, 0, 0.05)',
    '0px 4px 8px rgba(0, 0, 0, 0.08)',
    '0px 8px 16px rgba(0, 0, 0, 0.1)',
    // ... remaining shadows unchanged
  ],
});