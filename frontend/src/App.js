import React, { useEffect, Suspense } from 'react';
import {
  BrowserRouter as Router,
  Routes,
  Route,
  Navigate,
  useLocation
} from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { ThemeProvider } from '@mui/material/styles';
import CssBaseline from '@mui/material/CssBaseline';
import Box from '@mui/material/Box';
import Container from '@mui/material/Container';
import Typography from '@mui/material/Typography';
import Button from '@mui/material/Button';

// Auth Components
import Login from './components/auth/Login';
import Register from './components/auth/Register';
import PrivateRoute from './components/routing/PrivateRoute';
import AdminLogin from './components/auth/AdminLogin';

// Layout Components
import Dashboard from './components/dashboard/Dashboard';
import Navbar from './components/layout/Navbar';
import Sidebar from './components/layout/Sidebar';
import Footer from './components/layout/Footer';

// Add the Home component
import Home from './components/shared/Home';

// Shared/Public Components
import Contact from './components/shared/Contact';
import Courses from './components/shared/Courses';

// Admin Components
import UserManagement from './components/admin/UserManagement';
import SystemSettings from './components/admin/SystemSettings';
import AdminDashboard from './components/admin/AdminDashboard';
import RegistrationRequests from './components/admin/RegistrationRequests';

// Teacher Components
import CourseManagement from './components/teacher/CourseManagement';
import AssignmentCreation from './components/teacher/AssignmentCreation';
import GradeManagement from './components/teacher/GradeManagement';
import QuizCreation from './components/teacher/QuizCreation';

// Student Components
import MyCourses from './components/student/MyCourses';
import Assignments from './components/student/Assignments';
import Quizzes from './components/student/Quizzes';
import Grades from './components/student/Grades';

// Parent Components
import ChildrenProgress from './components/parent/ChildrenProgress';
import TeacherCommunication from './components/parent/TeacherCommunication';
import ParentDashboard from './components/parent/ParentDashboard';

// Shared Components
import Profile from './components/shared/Profile';
import Calendar from './components/shared/Calendar';
import Messages from './components/shared/Messages';
import Announcements from './components/shared/Announcements';

// Context
import { AuthProvider, useAuth } from './context/auth/AuthState';
import { AlertProvider } from './context/alert/AlertState';
import { SidebarProvider } from './context/sidebar/SidebarState';
import { GradeProvider } from './context/grade/GradeContext';

// Theme
import theme from './theme';

// Styles
import './App.css';
import './components/components.css';

// Add AuthDebug component to help diagnose auth issues
const AuthDebug = () => {
  const { isAuthenticated, user, loading, token } = useAuth();
  
  return (
    <Box sx={{ p: 4 }}>
      <Typography variant="h4" gutterBottom>Auth Debug</Typography>
      
      <Typography variant="h6">Authentication State:</Typography>
      <pre style={{ background: '#f5f5f5', padding: 16, borderRadius: 4 }}>
        {JSON.stringify({ isAuthenticated, loading, token: !!token }, null, 2)}
      </pre>
      
      {user && (
        <>
          <Typography variant="h6" mt={2}>User Info:</Typography>
          <pre style={{ background: '#f5f5f5', padding: 16, borderRadius: 4 }}>
            {JSON.stringify(user, null, 2)}
          </pre>
        </>
      )}
      
      <Box mt={4}>
        <Button 
          variant="contained" 
          color="primary"
          onClick={() => window.location.href = '/dashboard'}
          sx={{ mr: 2 }}
        >
          Go to Dashboard
        </Button>
        
        <Button 
          variant="outlined" 
          color="secondary"
          onClick={() => {
            localStorage.removeItem('token');
            window.location.href = '/login';
          }}
        >
          Clear Token & Logout
        </Button>
      </Box>
    </Box>
  );
};

// Layout component that conditionally renders Navbar, Sidebar, etc.
const Layout = ({ children }) => {
  const location = useLocation();
  const isAuthPage = ['/login', '/register', '/forgot-password'].includes(location.pathname);
  
  // Add homepage to the list of pages that don't need the sidebar
  const isFullWidthPage = ['/', '/login', '/register', '/forgot-password', '/about', '/contact', '/courses', '/privacy', '/nav-test'].includes(location.pathname);
  
  // Add a class to the container for auth pages and home page
  const containerClass = isAuthPage ? 'container auth-container' : 'container';

  return (
    <>
      {!isAuthPage && <Navbar />}
      <div className={containerClass}>
        {!isFullWidthPage && <Sidebar />}
        <main className={isFullWidthPage ? 'main-content full-width' : 'main-content'}>
          {children}
        </main>
      </div>
      {!isAuthPage && <Footer />}
    </>
  );
};

const App = () => {
  const { i18n, t } = useTranslation();

  useEffect(() => {
    // Check for saved language preference
    const savedLanguage = localStorage.getItem('language') || 'en';
    i18n.changeLanguage(savedLanguage);
  }, [i18n]);

  return (
    <ThemeProvider theme={theme}>
      <CssBaseline />
      <AuthProvider>
        <AlertProvider>
          <SidebarProvider>
            <GradeProvider>
              <Router>
                <div className="app">
                  <Routes>
                    <Route path="/login" element={<Login />} />
                    <Route path="/register" element={<Register />} />
                    
                    {/* All other routes wrapped with Layout component */}
                    <Route path="*" element={
                      <Layout>
                        <Routes>
                          {/* Home page */}
                          <Route path="/" element={<Home />} />
                          
                          {/* Public pages */}
                          <Route path="/contact" element={<Contact />} />
                          <Route path="/courses" element={<Courses />} />
                          <Route path="/auth-debug" element={<AuthDebug />} />
                          <Route path="/nav-test" element={
                            // Simple test page for navigation and language switcher
                            <Suspense fallback={<div>Loading...</div>}>
                              {React.createElement(React.lazy(() => import('./components/layout/NavbarTest')))}
                            </Suspense>
                          } />
                          <Route path="/about" element={
                            <Box sx={{ py: 6 }}>
                              <Container maxWidth="lg">
                                <Typography variant="h3" component="h1" fontWeight="bold" gutterBottom>
                                  {t('about.title', 'About Us')}
                                </Typography>
                                <Typography variant="body1" paragraph>
                                  {t('about.paragraph1', 'Our Ethiopian high school e-learning system provides comprehensive educational resources for students across Ethiopia. We aim to make quality education accessible to all students regardless of their location.')}
                                </Typography>
                                <Typography variant="body1" paragraph>
                                  {t('about.paragraph2', 'Our platform offers a wide range of courses aligned with the national curriculum, interactive learning materials, and tools for teachers to effectively manage their classes and track student progress.')}
                                </Typography>
                              </Container>
                            </Box>
                          } />
                          <Route path="/privacy" element={
                            <Box sx={{ py: 6 }}>
                              <Container maxWidth="lg">
                                <Typography variant="h3" component="h1" fontWeight="bold" gutterBottom>
                                  {t('privacy.title', 'Privacy Policy')}
                                </Typography>
                                <Typography variant="h5" gutterBottom sx={{ mt: 4 }}>
                                  {t('privacy.section1.title', 'Information Collection and Use')}
                                </Typography>
                                <Typography variant="body1" paragraph>
                                  {t('privacy.section1.content', 'We collect information to provide better services to our users. The types of information we collect include account information, usage information, and other information provided by you or obtained with your consent.')}
                                </Typography>
                                <Typography variant="h5" gutterBottom sx={{ mt: 4 }}>
                                  {t('privacy.section2.title', 'Data Security')}
                                </Typography>
                                <Typography variant="body1" paragraph>
                                  {t('privacy.section2.content', 'We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.')}
                                </Typography>
                                <Typography variant="h5" gutterBottom sx={{ mt: 4 }}>
                                  {t('privacy.section3.title', 'Changes to This Policy')}
                                </Typography>
                                <Typography variant="body1" paragraph>
                                  {t('privacy.section3.content', 'We may update our Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page.')}
                                </Typography>
                              </Container>
                            </Box>
                          } />
                          
                          {/* Dashboard */}
                          <Route 
                            path="/dashboard" 
                            element={
                              <PrivateRoute>
                                <Dashboard />
                              </PrivateRoute>
                            } 
                          />
                          
                          {/* Admin Routes */}
                          <Route 
                            path="/admin/users" 
                            element={
                              <PrivateRoute roles={['admin']}>
                                <UserManagement />
                              </PrivateRoute>
                            } 
                          />
                          <Route 
                            path="/admin/dashboard" 
                            element={
                              <PrivateRoute roles={['admin']}>
                                <AdminDashboard />
                              </PrivateRoute>
                            } 
                          />
                          <Route 
                            path="/admin/settings" 
                            element={
                              <PrivateRoute roles={['admin']}>
                                <SystemSettings />
                              </PrivateRoute>
                            } 
                          />
                          <Route 
                            path="/admin/login" 
                            element={
                              <PrivateRoute roles={['admin']}>
                                <AdminLogin />
                              </PrivateRoute>
                            } 
                          />
                          <Route 
                            path="/admin/registration-requests" 
                            element={
                              <PrivateRoute roles={['admin']}>
                                <RegistrationRequests />
                              </PrivateRoute>
                            } 
                          />
                          
                          {/* Teacher Routes */}
                          <Route 
                            path="/teacher/courses" 
                            element={
                              <PrivateRoute roles={['teacher']}>
                                <CourseManagement />
                              </PrivateRoute>
                            } 
                          />
                          <Route 
                            path="/teacher/assignments" 
                            element={
                              <PrivateRoute roles={['teacher']}>
                                <AssignmentCreation />
                              </PrivateRoute>
                            } 
                          />
                          <Route 
                            path="/teacher/grades" 
                            element={
                              <PrivateRoute roles={['teacher']}>
                                <GradeManagement />
                              </PrivateRoute>
                            } 
                          />
                          <Route 
                            path="/teacher/quizzes" 
                            element={
                              <PrivateRoute roles={['teacher']}>
                                <QuizCreation />
                              </PrivateRoute>
                            } 
                          />
                          
                          {/* Student Routes */}
                          <Route 
                            path="/student/courses" 
                            element={
                              <PrivateRoute roles={['student']}>
                                <MyCourses />
                              </PrivateRoute>
                            } 
                          />
                          <Route 
                            path="/student/assignments" 
                            element={
                              <PrivateRoute roles={['student']}>
                                <Assignments />
                              </PrivateRoute>
                            } 
                          />
                          <Route 
                            path="/student/quizzes" 
                            element={
                              <PrivateRoute roles={['student']}>
                                <Quizzes />
                              </PrivateRoute>
                            } 
                          />
                          <Route 
                            path="/student/grades" 
                            element={
                              <PrivateRoute roles={['student']}>
                                <Grades />
                              </PrivateRoute>
                            } 
                          />
                          
                          {/* Parent Routes */}
                          <Route 
                            path="/parent/progress" 
                            element={
                              <PrivateRoute roles={['parent']}>
                                <ChildrenProgress />
                              </PrivateRoute>
                            } 
                          />
                          <Route 
                            path="/parent/communication" 
                            element={
                              <PrivateRoute roles={['parent']}>
                                <TeacherCommunication />
                              </PrivateRoute>
                            } 
                          />
                          <Route 
                            path="/parent/dashboard" 
                            element={
                              <PrivateRoute roles={['parent']}>
                                <ParentDashboard />
                              </PrivateRoute>
                            } 
                          />
                          
                          {/* Shared Routes */}
                          <Route 
                            path="/profile" 
                            element={
                              <PrivateRoute>
                                <Profile />
                              </PrivateRoute>
                            } 
                          />
                          <Route 
                            path="/calendar" 
                            element={
                              <PrivateRoute>
                                <Calendar />
                              </PrivateRoute>
                            } 
                          />
                          <Route 
                            path="/messages" 
                            element={
                              <PrivateRoute>
                                <Messages />
                              </PrivateRoute>
                            } 
                          />
                          <Route 
                            path="/announcements" 
                            element={
                              <PrivateRoute>
                                <Announcements />
                              </PrivateRoute>
                            } 
                          />
                          
                          {/* Redirect to home if no route matches */}
                          <Route path="*" element={<Navigate replace to="/" />} />
                        </Routes>
                      </Layout>
                    } />
                  </Routes>
                </div>
              </Router>
            </GradeProvider>
          </SidebarProvider>
        </AlertProvider>
      </AuthProvider>
    </ThemeProvider>
  );
};

export default App;