<?php 

if (!class_exists('Course')) {

class Course {
   private $table_name;
   private $conn;

   private $course_id;
   private $title;
   private $title_am;
   private $title_ti;
   private $title_om;
   private $description;
   private $description_am;
   private $description_ti;
   private $description_om;
   private $cover_img;
   private $status;
   private $created_by;
   private $created_by_role;
   private $created_at;
   private $updated_at;

   // Learning objectives
   private $learning_objectives = [];

   function __construct($db_conn){
     $this->conn = $db_conn;
     $this->table_name = "courses";
   }

   function init($course_id) {
       try {
           $sql = 'SELECT * FROM ' . $this->table_name . ' WHERE course_id = ?';
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([$course_id]);
           
           if ($stmt->rowCount() == 1) {
               $course = $stmt->fetch();
               $this->course_id = $course['course_id'];
               $this->title = $course['title'];
               $this->title_am = $course['title_am'] ?? $course['title'];
               $this->title_ti = $course['title_ti'] ?? $course['title'];
               $this->title_om = $course['title_om'] ?? $course['title'];
               $this->description = $course['description'];
               $this->description_am = $course['description_am'] ?? $course['description'];
               $this->description_ti = $course['description_ti'] ?? $course['description'];
               $this->description_om = $course['description_om'] ?? $course['description'];
               $this->cover_img = $course['cover_img'] ?? '';
               $this->status = $course['status'];
               $this->created_by = $course['created_by'] ?? '';
               $this->created_by_role = $course['created_by_role'] ?? '';
               $this->created_at = $course['created_at'];
               $this->updated_at = $course['updated_at'] ?? $course['created_at'];
               return 1;
           }
           return 0;
       } catch(PDOException $e) {
           error_log("Course init error: " . $e->getMessage());
           return 0;
       }
   }

   function getData() {
       return array(
           'course_id' => $this->course_id,
           'title' => $this->title,
           'title_am' => $this->title_am ?? $this->title,
           'title_ti' => $this->title_ti ?? $this->title,
           'title_om' => $this->title_om ?? $this->title,
           'description' => $this->description,
           'description_am' => $this->description_am ?? $this->description,
           'description_ti' => $this->description_ti ?? $this->description,
           'description_om' => $this->description_om ?? $this->description,
           'cover_img' => $this->cover_img ?? '',
           'status' => $this->status,
           'created_by' => $this->created_by ?? '',
           'created_by_role' => $this->created_by_role ?? '',
           'created_at' => $this->created_at,
           'updated_at' => $this->updated_at ?? $this->created_at
       );
   }

   function getCourseById($cour_id){
       try {
          $sql = 'SELECT * FROM '. $this->table_name.' WHERE course_id=?';
          $stmt = $this->conn->prepare($sql);
          $res = $stmt->execute([$cour_id]);
          if ($res) {
            $course = $stmt->fetch();
            return $course;
          } else return 0;
       }catch(PDOException $e){
          return 0;
       }
   }

   function count(){
      try {
          $sql = 'SELECT course_id FROM '. $this->table_name;
          $stmt = $this->conn->prepare($sql);
          $res = $stmt->execute();

          return $stmt->rowCount();
       }catch(PDOException $e){
           return 0;
       }
   }

   function getSome($offset, $num){

      try {
          $sql = "SELECT * FROM ". $this->table_name ." ORDER BY course_id desc LIMIT :offset, :l";
          $stmt = $this->conn->prepare($sql);
          $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
          $stmt->bindParam(':l', $num, PDO::PARAM_INT);
           $stmt->execute();

          // $sql = "SELECT * FROM post LIMIT :offset, :l";
          if($stmt->rowCount() > 0) {
               $courses = $stmt->fetchAll();

               return $courses;
         }else return 0;
       }catch(PDOException $e){
           return 0;
       }
   }

   function getByInstructorId($instructor_id){
      try {
          $sql = 'SELECT * FROM '. $this->table_name.' WHERE instructor_id=?';
          $stmt = $this->conn->prepare($sql);
          $res = $stmt->execute([$instructor_id]);
          if ($res) {
            $courses = $stmt->fetchAll();
            return $courses;
          } else return 0;
       }catch(PDOException $e){
          return 0;
       }
   }

   function search($key, $offset = 0, $limit = 12) {
      try {
          $sql = 'SELECT * FROM '. $this->table_name.' 
                  WHERE (title LIKE ? OR description LIKE ?) 
                  AND status = "public"
                  ORDER BY created_at DESC
                  LIMIT ?, ?';
          
          $search_term = "%{$key}%";
          $stmt = $this->conn->prepare($sql);
          $stmt->bindParam(1, $search_term, PDO::PARAM_STR);
          $stmt->bindParam(2, $search_term, PDO::PARAM_STR);
          $stmt->bindParam(3, $offset, PDO::PARAM_INT);
          $stmt->bindParam(4, $limit, PDO::PARAM_INT);
          $stmt->execute();

          if ($stmt->rowCount() > 0) {
              return $stmt->fetchAll(PDO::FETCH_ASSOC);
          }
          return [];
       } catch(PDOException $e) {
          error_log("Course search error: " . $e->getMessage());
          return [];
       }
   }

   function getTopics($course_id){
       try {
          $sql = 'SELECT * FROM topic WHERE course_id=?';
          $stmt = $this->conn->prepare($sql);
          $res = $stmt->execute([$course_id]);
          if ($res) {
            $topics = $stmt->fetchAll();
            return $topics;
          } else return 0;
       }catch(PDOException $e){
          return 0;
       }
   }

   function getChapters($course_id){
       try {
          $sql = 'SELECT * FROM chapter WHERE course_id=?';
          $stmt = $this->conn->prepare($sql);
          $res = $stmt->execute([$course_id]);
          if ($res) {
            $chapters = $stmt->fetchAll();
            return $chapters;
          } else return 0;
       }catch(PDOException $e){
          return 0;
       }
   }

   function getContent($course_id, $chapter_id, $topic_id){
       try {
          $sql = 'SELECT * FROM topic WHERE course_id=? AND chapter_id=? AND topic_id=?';
          $stmt = $this->conn->prepare($sql);
          $res = $stmt->execute([$course_id, $chapter_id, $topic_id]);
          if ($res) {
            $content = $stmt->fetch();
            return $content;
          } else return 0;
       }catch(PDOException $e){
          return 0;
       }
   }

   function pageExes($course_id, $chapter_id){
       try {
          $sql = 'SELECT * FROM topic WHERE course_id=? AND chapter_id=?';
          $stmt = $this->conn->prepare($sql);
          $res = $stmt->execute([$course_id, $chapter_id]);
          if ($res) {
            $topics_count = $stmt->rowCount();
            return $topics_count;
          } else return 0;
       }catch(PDOException $e){
          return 0;
       }
   }

   function insert($data){
       try {
          $sql = 'INSERT INTO '. $this->table_name.'(title, description, instructor_id, status, cover_img) VALUES(?,?,?,?,?)';
          $stmt = $this->conn->prepare($sql);
          $res = $stmt->execute($data);
          return $res;
       }catch(PDOException $e){
          return 0;
       }
   }
   
   function getCountByInstructorId($instructor_id){
      try {
          $sql = 'SELECT COUNT(*) FROM '. $this->table_name.' WHERE instructor_id=?';
          $stmt = $this->conn->prepare($sql);
          $res = $stmt->execute([$instructor_id]);
          if ($res) {
            $count = $stmt->fetchColumn();
            return $count;
          } else return 0;
       }catch(PDOException $e){
          return 0;
       }
   }

   function getSomeByInstructorId($offset, $num, $instructor_id){
      try {
          $sql = "SELECT * FROM ". $this->table_name ." WHERE instructor_id=? ORDER BY course_id desc LIMIT :offset, :l";
          $stmt = $this->conn->prepare($sql);
          $stmt->bindParam(1, $instructor_id, PDO::PARAM_INT);
          $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
          $stmt->bindParam(':l', $num, PDO::PARAM_INT);
          $stmt->execute();

          if($stmt->rowCount() > 0) {
               $courses = $stmt->fetchAll();
               return $courses;
         }else return 0;
       }catch(PDOException $e){
           return 0;
       }
   }

   // Create a new course
   public function create($data) {
       try {
           $this->conn->beginTransaction();
           
           $sql = "INSERT INTO " . $this->table_name . " (
               title, title_am, title_ti, title_om,
               description, description_am, description_ti, description_om,
               cover_img, status, created_by, created_by_role
           ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
           
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([
               $data['title'],
               $data['title_am'] ?? null,
               $data['title_ti'] ?? null,
               $data['title_om'] ?? null,
               $data['description'],
               $data['description_am'] ?? null,
               $data['description_ti'] ?? null,
               $data['description_om'] ?? null,
               $data['cover_img'] ?? 'default_course.jpg',
               $data['status'] ?? 'public',
               $data['created_by'],
               $data['created_by_role']
           ]);
           
           $course_id = $this->conn->lastInsertId();
           
           // Add learning objectives if provided
           if (!empty($data['learning_objectives'])) {
               $this->addLearningObjectives($course_id, $data['learning_objectives']);
           }
           
           $this->conn->commit();
           return $course_id;
       } catch(PDOException $e) {
           $this->conn->rollBack();
           error_log("Course creation error: " . $e->getMessage());
           return false;
       }
   }

   // Add learning objectives to a course
   private function addLearningObjectives($course_id, $objectives) {
       try {
           $sql = "INSERT INTO course_objective (
               course_id, objective_number, description,
               description_am, description_ti, description_om,
               bloom_level
           ) VALUES (?, ?, ?, ?, ?, ?, ?)";
           
           $stmt = $this->conn->prepare($sql);
           
           foreach ($objectives as $objective) {
               $stmt->execute([
                   $course_id,
                   $objective['number'],
                   $objective['description'],
                   $objective['description_am'] ?? null,
                   $objective['description_ti'] ?? null,
                   $objective['description_om'] ?? null,
                   $objective['bloom_level']
               ]);
           }
           
           return true;
       } catch(PDOException $e) {
           error_log("Add learning objectives error: " . $e->getMessage());
           return false;
       }
   }

   // Get course by ID with all related data
   public function getById($course_id) {
       try {
           // Get course details
           $sql = "SELECT * FROM " . $this->table_name . " WHERE course_id = ?";
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([$course_id]);
           $course = $stmt->fetch(PDO::FETCH_ASSOC);
           
           if ($course) {
               // Get learning objectives
               $sql = "SELECT * FROM course_objective 
                       WHERE course_id = ? 
                       ORDER BY objective_number ASC";
               $stmt = $this->conn->prepare($sql);
               $stmt->execute([$course_id]);
               $course['learning_objectives'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
               
               // Get chapters
               $sql = "SELECT * FROM chapter 
                       WHERE course_id = ? 
                       ORDER BY week_number ASC, `order` ASC";
               $stmt = $this->conn->prepare($sql);
               $stmt->execute([$course_id]);
               $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);
               
               // Get topics for each chapter
               foreach ($chapters as &$chapter) {
                   $sql = "SELECT * FROM topic 
                           WHERE chapter_id = ? 
                           ORDER BY `order` ASC";
                   $stmt = $this->conn->prepare($sql);
                   $stmt->execute([$chapter['chapter_id']]);
                   $chapter['topics'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
               }
               
               $course['chapters'] = $chapters;
           }
           
           return $course;
       } catch(PDOException $e) {
           error_log("Get course by ID error: " . $e->getMessage());
           return false;
       }
   }

   // Get all courses with optional filters
   public function getAll($filters = []) {
       try {
           $sql = "SELECT * FROM " . $this->table_name;
           $params = [];
           
           if (!empty($filters)) {
               $conditions = [];
               
               if (isset($filters['status'])) {
                   $conditions[] = "status = ?";
                   $params[] = $filters['status'];
               }
               
               if (isset($filters['created_by'])) {
                   $conditions[] = "created_by = ?";
                   $params[] = $filters['created_by'];
               }
               
               if (isset($filters['created_by_role'])) {
                   $conditions[] = "created_by_role = ?";
                   $params[] = $filters['created_by_role'];
               }
               
               if (!empty($conditions)) {
                   $sql .= " WHERE " . implode(" AND ", $conditions);
               }
           }
           
           $sql .= " ORDER BY created_at DESC";
           
           $stmt = $this->conn->prepare($sql);
           $stmt->execute($params);
           return $stmt->fetchAll(PDO::FETCH_ASSOC);
       } catch(PDOException $e) {
           error_log("Get all courses error: " . $e->getMessage());
           return false;
       }
   }

   // Update course
   public function update($course_id, $data) {
       try {
           $this->conn->beginTransaction();
           
           $sql = "UPDATE " . $this->table_name . " SET 
               title = ?, title_am = ?, title_ti = ?, title_om = ?,
               description = ?, description_am = ?, description_ti = ?, description_om = ?,
               cover_img = ?, status = ?,
               updated_at = NOW()
               WHERE course_id = ?";
           
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([
               $data['title'],
               $data['title_am'] ?? null,
               $data['title_ti'] ?? null,
               $data['title_om'] ?? null,
               $data['description'],
               $data['description_am'] ?? null,
               $data['description_ti'] ?? null,
               $data['description_om'] ?? null,
               $data['cover_img'] ?? 'default_course.jpg',
               $data['status'],
               $course_id
           ]);
           
           // Update learning objectives if provided
           if (isset($data['learning_objectives'])) {
               $this->updateLearningObjectives($course_id, $data['learning_objectives']);
           }
           
           $this->conn->commit();
           return true;
       } catch(PDOException $e) {
           $this->conn->rollBack();
           error_log("Update course error: " . $e->getMessage());
           return false;
       }
   }

   // Update learning objectives
   public function updateLearningObjectives($course_id, $objectives) {
       try {
           // Delete existing objectives
           $sql = "DELETE FROM course_objective WHERE course_id = ?";
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([$course_id]);
           
           // Add new objectives
           return $this->addLearningObjectives($course_id, $objectives);
       } catch(PDOException $e) {
           error_log("Update learning objectives error: " . $e->getMessage());
           return false;
       }
   }

   // Toggle course status
   public function toggleStatus($course_id) {
       try {
           $sql = "UPDATE " . $this->table_name . " 
                   SET status = CASE 
                       WHEN status = 'public' THEN 'private'
                       ELSE 'public'
                   END,
                   updated_at = NOW()
                   WHERE course_id = ?";
           
           $stmt = $this->conn->prepare($sql);
           return $stmt->execute([$course_id]);
       } catch(PDOException $e) {
           error_log("Toggle course status error: " . $e->getMessage());
           return false;
       }
   }

   // Delete course
   public function delete($course_id) {
       try {
           $this->conn->beginTransaction();
           
           // Delete learning objectives
           $sql = "DELETE FROM course_objective WHERE course_id = ?";
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([$course_id]);
           
           // Delete course
           $sql = "DELETE FROM " . $this->table_name . " WHERE course_id = ?";
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([$course_id]);
           
           $this->conn->commit();
           return true;
       } catch(PDOException $e) {
           $this->conn->rollBack();
           error_log("Delete course error: " . $e->getMessage());
           return false;
       }
   }

   // Get course statistics
   public function getStatistics($course_id) {
       try {
           $sql = "SELECT 
               (SELECT COUNT(*) FROM enrolled_student WHERE course_id = ?) as total_students,
               (SELECT COUNT(*) FROM chapter WHERE course_id = ?) as total_chapters,
               (SELECT COUNT(*) FROM quiz WHERE course_id = ?) as total_quizzes,
               (SELECT AVG(score) FROM quiz_attempt qa 
                JOIN quiz q ON qa.quiz_id = q.quiz_id 
                WHERE q.course_id = ?) as average_quiz_score";
               
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([$course_id, $course_id, $course_id, $course_id]);
           return $stmt->fetch(PDO::FETCH_ASSOC);
       } catch(PDOException $e) {
           error_log("Get course statistics error: " . $e->getMessage());
           return false;
       }
   }

   // Get student progress
   public function getStudentProgress($course_id, $student_id) {
       try {
           $sql = 'SELECT progress FROM student_course WHERE course_id = ? AND student_id = ?';
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([$course_id, $student_id]);
           
           if ($stmt->rowCount() > 0) {
               $result = $stmt->fetch();
               return $result['progress'];
           }
           return 0;
       } catch(PDOException $e) {
           error_log("Get student progress error: " . $e->getMessage());
           return 0;
       }
   }

   // Update student progress
   public function updateStudentProgress($course_id, $student_id, $progress) {
       try {
           $sql = 'UPDATE student_course SET progress = ? WHERE course_id = ? AND student_id = ?';
           $stmt = $this->conn->prepare($sql);
           return $stmt->execute([$progress, $course_id, $student_id]);
       } catch(PDOException $e) {
           error_log("Update student progress error: " . $e->getMessage());
           return false;
       }
   }

   // Create student progress record
   public function createStudentProgress($course_id, $student_id, $progress = 0) {
       try {
           $sql = 'INSERT INTO student_course (course_id, student_id, progress) VALUES (?, ?, ?)';
           $stmt = $this->conn->prepare($sql);
           return $stmt->execute([$course_id, $student_id, $progress]);
       } catch(PDOException $e) {
           error_log("Create student progress error: " . $e->getMessage());
           return false;
       }
   }

   // Check if page exists
   public function isPageExes($course_id, $chapter_id, $topic_id) {
       try {
           $sql = 'SELECT * FROM topic WHERE course_id = ? AND chapter_id = ? AND topic_id = ?';
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([$course_id, $chapter_id, $topic_id]);
           return $stmt->rowCount();
       } catch(PDOException $e) {
           error_log("Check page exists error: " . $e->getMessage());
           return 0;
       }
   }

   // Get enrolled courses for student
   public function getSomeEnrolled($student_id) {
       try {
           $sql = 'SELECT c.* FROM course c 
                  INNER JOIN student_course sc ON c.course_id = sc.course_id 
                  WHERE sc.student_id = ? AND c.status = "public"
                  ORDER BY sc.last_accessed_at DESC';
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([$student_id]);
           return $stmt->fetchAll(PDO::FETCH_ASSOC);
       } catch(PDOException $e) {
           error_log("Get enrolled courses error: " . $e->getMessage());
           return array();
       }
   }

   // Get total search results count
   public function getSearchCount($key) {
      try {
          $sql = 'SELECT COUNT(*) FROM '. $this->table_name.' 
                  WHERE (title LIKE ? OR description LIKE ?) 
                  AND status = "public"';
          
          $search_term = "%{$key}%";
          $stmt = $this->conn->prepare($sql);
          $stmt->bindParam(1, $search_term, PDO::PARAM_STR);
          $stmt->bindParam(2, $search_term, PDO::PARAM_STR);
          $stmt->execute();

          return $stmt->fetchColumn();
       } catch(PDOException $e) {
          error_log("Course search count error: " . $e->getMessage());
          return 0;
       }
   }

   function getTopPerforming($limit = 5) {
       try {
           $sql = "SELECT c.*, COUNT(lpe.student_id) as enrollments, 
                          (SUM(CASE WHEN lpe.progress = 100 THEN 1 ELSE 0 END) * 100.0 / COUNT(lpe.student_id)) as completion_rate
                   FROM " . $this->table_name . " c
                   JOIN learning_path_enrollments lpe ON c.course_id = lpe.path_id -- Assuming course_id is equivalent to path_id for enrollments
                   GROUP BY c.course_id
                   ORDER BY enrollments DESC, completion_rate DESC
                   LIMIT :limit";
           $stmt = $this->conn->prepare($sql);
           $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
           $stmt->execute();
           return $stmt->fetchAll(PDO::FETCH_ASSOC);
       } catch(PDOException $e) {
           error_log("Course getTopPerforming error: " . $e->getMessage());
           return [];
       }
   }

   function getPopularity() {
       try {
           $sql = "SELECT c.title, COUNT(lpe.student_id) as enrollments
                   FROM course c
                   JOIN learning_path_enrollments lpe ON c.course_id = lpe.path_id
                   GROUP BY c.course_id, c.title
                   ORDER BY enrollments DESC
                   LIMIT 5";
           $stmt = $this->conn->prepare($sql);
           $stmt->execute();
           return $stmt->fetchAll(PDO::FETCH_ASSOC);
       } catch(PDOException $e) {
           error_log("Course getPopularity error: " . $e->getMessage());
           return [];
       }
   }

   function getCourses($offset = 0, $limit = 10) {
       try {
           $sql = 'SELECT * FROM ' . $this->table_name . ' ORDER BY created_at DESC LIMIT :offset, :limit';
           $stmt = $this->conn->prepare($sql);
           $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
           $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
           $stmt->execute();
           return $stmt->fetchAll();
       } catch(PDOException $e) {
           error_log("Course getCourses error: " . $e->getMessage());
           throw new Exception("Failed to retrieve courses: " . $e->getMessage());
       }
   }

   function getCourseCount() {
       try {
           $sql = 'SELECT COUNT(*) as count FROM ' . $this->table_name;
           $stmt = $this->conn->prepare($sql);
           $stmt->execute();
           $result = $stmt->fetch();
           return $result['count'];
       } catch(PDOException $e) {
           error_log("Course getCourseCount error: " . $e->getMessage());
           throw new Exception("Failed to count courses: " . $e->getMessage());
       }
   }

   function getStudentCourses($student_id, $offset = 0, $limit = 10) {
       try {
           $sql = 'SELECT c.*, 
                   (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.course_id AND student_id = ?) as is_enrolled
                   FROM ' . $this->table_name . ' c
                   ORDER BY c.created_at DESC
                   LIMIT :offset, :limit';
           $stmt = $this->conn->prepare($sql);
           $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
           $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
           $stmt->execute([$student_id]);
           return $stmt->fetchAll();
       } catch(PDOException $e) {
           error_log("Course getStudentCourses error: " . $e->getMessage());
           throw new Exception("Failed to retrieve student courses: " . $e->getMessage());
       }
   }

   function getStudentCourseCount($student_id) {
       try {
           $sql = 'SELECT COUNT(*) as count FROM ' . $this->table_name;
           $stmt = $this->conn->prepare($sql);
           $stmt->execute();
           $result = $stmt->fetch();
           return $result['count'];
       } catch(PDOException $e) {
           error_log("Course getStudentCourseCount error: " . $e->getMessage());
           throw new Exception("Failed to count student courses: " . $e->getMessage());
       }
   }

   function enrollStudent($student_id, $course_id) {
       try {
           // Check if already enrolled
           $sql = 'SELECT COUNT(*) as count FROM course_enrollments 
                   WHERE student_id = ? AND course_id = ?';
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([$student_id, $course_id]);
           $result = $stmt->fetch();
           
           if ($result['count'] > 0) {
               throw new Exception("Already enrolled in this course");
           }

           // Enroll student
           $sql = 'INSERT INTO course_enrollments (student_id, course_id, enrolled_at) 
                   VALUES (?, ?, NOW())';
           $stmt = $this->conn->prepare($sql);
           return $stmt->execute([$student_id, $course_id]);
       } catch(PDOException $e) {
           error_log("Course enrollStudent error: " . $e->getMessage());
           throw new Exception("Failed to enroll student: " . $e->getMessage());
       }
   }

   function unenrollStudent($student_id, $course_id) {
       try {
           $sql = 'DELETE FROM course_enrollments 
                   WHERE student_id = ? AND course_id = ?';
           $stmt = $this->conn->prepare($sql);
           return $stmt->execute([$student_id, $course_id]);
       } catch(PDOException $e) {
           error_log("Course unenrollStudent error: " . $e->getMessage());
           throw new Exception("Failed to unenroll student: " . $e->getMessage());
       }
   }

   function getEnrolledStudents($course_id) {
       try {
           $sql = 'SELECT s.* FROM students s
                   JOIN course_enrollments ce ON s.student_id = ce.student_id
                   WHERE ce.course_id = ?';
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([$course_id]);
           return $stmt->fetchAll();
       } catch(PDOException $e) {
           error_log("Course getEnrolledStudents error: " . $e->getMessage());
           throw new Exception("Failed to retrieve enrolled students: " . $e->getMessage());
       }
   }

   function getEnrolledStudentCount($course_id) {
       try {
           $sql = 'SELECT COUNT(*) as count FROM course_enrollments WHERE course_id = ?';
           $stmt = $this->conn->prepare($sql);
           $stmt->execute([$course_id]);
           $result = $stmt->fetch();
           return $result['count'];
       } catch(PDOException $e) {
           error_log("Course getEnrolledStudentCount error: " . $e->getMessage());
           throw new Exception("Failed to count enrolled students: " . $e->getMessage());
       }
   }
} // Class END

} // End if (!class_exists('Course'))