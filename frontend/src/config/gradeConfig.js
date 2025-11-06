/**
 * Grade System Configuration
 * 
 * This file contains the configuration for different grade levels and their
 * corresponding learning materials, assessments, and UI experiences.
 */

// Grade system mapping - allows for mapping between numerical grades and semantic levels
export const gradeSystemMapping = {
  // Elementary School
  1: { level: 'elementary', tier: 'lower', name: 'Grade 1' },
  2: { level: 'elementary', tier: 'lower', name: 'Grade 2' },
  3: { level: 'elementary', tier: 'lower', name: 'Grade 3' },
  4: { level: 'elementary', tier: 'upper', name: 'Grade 4' },
  5: { level: 'elementary', tier: 'upper', name: 'Grade 5' },
  6: { level: 'elementary', tier: 'upper', name: 'Grade 6' },
  
  // Middle School
  7: { level: 'middle', tier: 'lower', name: 'Grade 7' },
  8: { level: 'middle', tier: 'upper', name: 'Grade 8' },
  
  // High School
  9: { level: 'high', tier: 'lower', name: 'Grade 9' },
  10: { level: 'high', tier: 'lower', name: 'Grade 10' },
  11: { level: 'high', tier: 'upper', name: 'Grade 11' },
  12: { level: 'high', tier: 'upper', name: 'Grade 12' }
};

// Subject configuration by grade level
export const subjectsByGradeLevel = {
  // Elementary School
  elementary: {
    // Core subjects for all elementary grades
    core: [
      { id: 'math', name: 'Mathematics', icon: 'Calculate' },
      { id: 'language', name: 'Language', icon: 'MenuBook' },
      { id: 'science', name: 'Science', icon: 'Science' },
      { id: 'social', name: 'Social Studies', icon: 'Public' },
      { id: 'art', name: 'Art', icon: 'Palette' }
    ],
    // Additional subjects for upper elementary (grades 4-6)
    upper: [
      { id: 'computer', name: 'Computer Basics', icon: 'Computer' },
      { id: 'health', name: 'Health', icon: 'LocalHospital' }
    ]
  },
  
  // Middle School
  middle: {
    core: [
      { id: 'math', name: 'Mathematics', icon: 'Calculate' },
      { id: 'language', name: 'Language Arts', icon: 'MenuBook' },
      { id: 'science', name: 'Science', icon: 'Science' },
      { id: 'social', name: 'Social Studies', icon: 'Public' },
      { id: 'health', name: 'Health Education', icon: 'LocalHospital' }
    ],
    electives: [
      { id: 'art', name: 'Fine Arts', icon: 'Palette' },
      { id: 'computer', name: 'Computer Science', icon: 'Computer' },
      { id: 'music', name: 'Music', icon: 'MusicNote' },
      { id: 'pe', name: 'Physical Education', icon: 'FitnessCenter' }
    ]
  },
  
  // High School
  high: {
    core: [
      { id: 'math', name: 'Mathematics', icon: 'Calculate' },
      { id: 'language', name: 'Language Arts', icon: 'MenuBook' },
      { id: 'science', name: 'Science', icon: 'Science' },
      { id: 'social', name: 'Social Studies', icon: 'Public' }
    ],
    electives: [
      { id: 'art', name: 'Fine Arts', icon: 'Palette' },
      { id: 'computer', name: 'Computer Science', icon: 'Computer' },
      { id: 'music', name: 'Music', icon: 'MusicNote' },
      { id: 'pe', name: 'Physical Education', icon: 'FitnessCenter' },
      { id: 'foreign', name: 'Foreign Language', icon: 'Translate' },
      { id: 'business', name: 'Business Studies', icon: 'Business' },
      { id: 'health', name: 'Health Sciences', icon: 'LocalHospital' }
    ],
    
    // Advanced Placement/Advanced courses for upper high school
    advanced: [
      { id: 'ap-math', name: 'AP Mathematics', icon: 'Calculate', prerequisite: 'math' },
      { id: 'ap-science', name: 'AP Science', icon: 'Science', prerequisite: 'science' },
      { id: 'ap-language', name: 'AP Language', icon: 'MenuBook', prerequisite: 'language' },
      { id: 'ap-social', name: 'AP Social Studies', icon: 'Public', prerequisite: 'social' }
    ]
  }
};

// Grading scales for different educational levels
export const gradingScales = {
  elementary: {
    scale: [
      { min: 90, max: 100, value: 'Excellent', gpa: 4.0 },
      { min: 80, max: 89, value: 'Very Good', gpa: 3.5 },
      { min: 70, max: 79, value: 'Good', gpa: 3.0 },
      { min: 60, max: 69, value: 'Satisfactory', gpa: 2.0 },
      { min: 0, max: 59, value: 'Needs Improvement', gpa: 0.0 }
    ],
    description: 'Elementary grades use descriptive terms instead of letter grades to provide more constructive feedback for young learners.'
  },
  
  middle: {
    scale: [
      { min: 90, max: 100, value: 'A', gpa: 4.0 },
      { min: 80, max: 89, value: 'B', gpa: 3.0 },
      { min: 70, max: 79, value: 'C', gpa: 2.0 },
      { min: 60, max: 69, value: 'D', gpa: 1.0 },
      { min: 0, max: 59, value: 'F', gpa: 0.0 }
    ],
    description: 'Middle school uses a simplified letter grade system to introduce students to more formalized assessment.'
  },
  
  high: {
    scale: [
      { min: 93, max: 100, value: 'A', gpa: 4.0 },
      { min: 90, max: 92, value: 'A-', gpa: 3.7 },
      { min: 87, max: 89, value: 'B+', gpa: 3.3 },
      { min: 83, max: 86, value: 'B', gpa: 3.0 },
      { min: 80, max: 82, value: 'B-', gpa: 2.7 },
      { min: 77, max: 79, value: 'C+', gpa: 2.3 },
      { min: 73, max: 76, value: 'C', gpa: 2.0 },
      { min: 70, max: 72, value: 'C-', gpa: 1.7 },
      { min: 67, max: 69, value: 'D+', gpa: 1.3 },
      { min: 63, max: 66, value: 'D', gpa: 1.0 },
      { min: 60, max: 62, value: 'D-', gpa: 0.7 },
      { min: 0, max: 59, value: 'F', gpa: 0.0 }
    ],
    description: 'High school uses a more detailed grading scale with plus/minus modifiers to better distinguish levels of achievement.'
  }
};

// Assessment types for different grade levels
export const assessmentTypes = {
  elementary: [
    { id: 'quiz', name: 'Quiz', weight: 20, icon: 'Quiz' },
    { id: 'classwork', name: 'Classwork', weight: 30, icon: 'Assignment' },
    { id: 'homework', name: 'Homework', weight: 20, icon: 'Home' },
    { id: 'project', name: 'Project', weight: 30, icon: 'Science' }
  ],
  middle: [
    { id: 'quiz', name: 'Quiz', weight: 20, icon: 'Quiz' },
    { id: 'test', name: 'Test', weight: 25, icon: 'School' },
    { id: 'assignment', name: 'Assignment', weight: 20, icon: 'Assignment' },
    { id: 'project', name: 'Project', weight: 25, icon: 'Science' },
    { id: 'participation', name: 'Participation', weight: 10, icon: 'People' }
  ],
  high: [
    { id: 'quiz', name: 'Quiz', weight: 15, icon: 'Quiz' },
    { id: 'test', name: 'Test', weight: 25, icon: 'School' },
    { id: 'exam', name: 'Exam', weight: 30, icon: 'MenuBook' },
    { id: 'assignment', name: 'Assignment', weight: 15, icon: 'Assignment' },
    { id: 'project', name: 'Project', weight: 15, icon: 'Science' }
  ]
};

// Function to determine if a student has access to a subject based on grade level
export const hasAccessToSubject = (gradeLevel, subjectId) => {
  // Default subject accessibility by grade level
  const subjectAccess = {
    // Elementary subjects
    'math-elementary': [1, 2, 3, 4, 5, 6],
    'reading': [1, 2, 3, 4, 5, 6],
    'science-elementary': [1, 2, 3, 4, 5, 6],
    'social-studies-elementary': [1, 2, 3, 4, 5, 6],
    'art': [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
    'music': [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
    'pe': [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
    
    // Middle school subjects
    'math-middle': [7, 8],
    'english-middle': [7, 8],
    'science-middle': [7, 8],
    'social-studies-middle': [7, 8],
    'computer': [7, 8, 9, 10, 11, 12],
    'foreign-language': [7, 8, 9, 10, 11, 12],
    
    // High school subjects
    'algebra': [9, 10],
    'geometry': [9, 10],
    'precalculus': [10, 11],
    'calculus': [11, 12],
    'biology': [9],
    'chemistry': [10],
    'physics': [11, 12],
    'world-history': [9],
    'us-history': [10],
    'government': [11],
    'economics': [12],
    'english-9': [9],
    'english-10': [10],
    'english-11': [11],
    'english-12': [12]
  };
  
  return subjectAccess[subjectId]?.includes(gradeLevel) || false;
};

// Get grade info based on percentage and grade level
export const getGradeInfo = (percentage, gradeLevel) => {
  if (!gradeLevel) return null;
  
  const gradeInfo = gradeSystemMapping[gradeLevel];
  if (!gradeInfo) return null;
  
  const scale = gradingScales[gradeInfo.level].scale;
  
  for (const grade of scale) {
    if (percentage >= grade.min && percentage <= grade.max) {
      return grade.value;
    }
  }
  
  return null;
};

export default {
  gradeSystemMapping,
  subjectsByGradeLevel,
  gradingScales,
  assessmentTypes,
  hasAccessToSubject,
  getGradeInfo
}; 