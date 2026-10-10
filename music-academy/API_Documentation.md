# Baritone Music Academy - RESTful API Documentation (v1)

This documentation provides a comprehensive guide to consuming the **Laravel 13 API** backend for Baritone Music Academy.

---

## 🌟 Base Configuration

- **API Base URL**: `http://localhost:8000/api/v1`
- **Default Headers**:
  ```http
  Accept: application/json
  Content-Type: application/json
  ```
- **Authenticated Requests**: Include the Sanctum Bearer token in the `Authorization` header:
  ```http
  Authorization: Bearer <YOUR_PERSONAL_ACCESS_TOKEN>
  ```

---

## 🔐 1. Authentication Endpoints

### 1.1 Register
- **URL**: `POST /api/v1/auth/register`
- **Payload**:
  ```json
  {
    "name": "Alex Mercer",
    "email": "alex@example.com",
    "password": "SecurePassword123!",
    "password_confirmation": "SecurePassword123!",
    "role": "student",
    "phone": "+254712345678"
  }
  ```
- **Response (201 Created)**:
  ```json
  {
    "message": "User registered successfully.",
    "user": {
      "id": 10,
      "name": "Alex Mercer",
      "email": "alex@example.com",
      "role": "student"
    },
    "token": "1|abc12345tokenstring..."
  }
  ```

### 1.2 Login
- **URL**: `POST /api/v1/auth/login`
- **Payload**:
  ```json
  {
    "email": "alex@example.com",
    "password": "SecurePassword123!"
  }
  ```
- **Response (200 OK)**:
  ```json
  {
    "message": "Login successful.",
    "user": { ... },
    "token": "2|xyz67890tokenstring..."
  }
  ```

### 1.3 Get Authenticated User Profile
- **URL**: `GET /api/v1/auth/me`
- **Headers**: `Authorization: Bearer <token>`
- **Response (200 OK)**: Returns profile details, enrolled course counts, role, avatar, and active status.

### 1.4 Logout
- **URL**: `POST /api/v1/auth/logout`
- **Headers**: `Authorization: Bearer <token>`
- **Response (200 OK)**: Revokes the current access token.

---

## 🎻 2. Public Catalog & Courses

### 2.1 List Published Courses
- **URL**: `GET /api/v1/courses`
- **Query Params**:
  - `instrument`: Filter by instrument slug (e.g. `piano`, `violin`)
  - `level`: Filter by level (`beginner`, `intermediate`, `advanced`)
  - `search`: Search by course title or instructor name
  - `page`: Pagination page number

### 2.2 Course Details
- **URL**: `GET /api/v1/courses/{slug}`
- **Response**: Full course syllabus, instructor bio, preview lessons, reviews, and tuition price.

### 2.3 List Instruments
- **URL**: `GET /api/v1/instruments`
- **Response**: List of all musical instruments and category groupings.

---

## 🎓 3. Student Classroom & Progression

### 3.1 List Enrolled Courses
- **URL**: `GET /api/v1/student/courses`
- **Response**: Returns courses the student is actively enrolled in, with progress percentage.

### 3.2 Enroll in a Course
- **URL**: `POST /api/v1/student/courses/{course_id}/enroll`

### 3.3 Classroom Syllabus & Content
- **URL**: `GET /api/v1/student/classroom/{course_slug}`
- **Response**: Unlocked lessons, video streams, audio attachments, MIDI scores, assignments, and quizzes.

### 3.4 Mark Lesson Complete / Toggle
- **URL**: `POST /api/v1/student/classroom/{course_slug}/lessons/{lesson_id}/toggle`

### 3.5 Submit Practice Assignment
- **URL**: `POST /api/v1/student/assignments/{assignment_id}/submit`
- **Payload (multipart/form-data)**:
  - `notes`: (string) Practice notes or questions
  - `media_file`: (audio/video recording)

### 3.6 Quizzes
- **List Quizzes**: `GET /api/v1/student/quizzes`
- **Take Quiz**: `GET /api/v1/student/quizzes/{quiz_id}/take`
- **Submit Quiz**: `POST /api/v1/student/quizzes/{quiz_id}/submit`
  ```json
  {
    "answers": {
      "1": 4,
      "2": 8
    }
  }
  ```
- **Quiz Results**: `GET /api/v1/student/quizzes/{quiz_id}/results/{attempt_id}`

### 3.7 Diplomas & Certificates
- **URL**: `GET /api/v1/student/certificates`
- **Public Verification**: `GET /api/v1/certificates/verify/{certificate_code}`

---

## 💳 4. Multi-Gateway Checkout & Payments

### 4.1 Checkout Summary
- **URL**: `GET /api/v1/checkout/{course_slug}/summary`
- **Response**: Returns course balance, tuition price, payment history, and enabled payment methods (`mpesa`, `stripe`, `paypal`, `cash`).

### 4.2 M-Pesa STK Push (Daraja API)
- **URL**: `POST /api/v1/checkout/mpesa/stk-push`
- **Payload**:
  ```json
  {
    "course_id": 1,
    "phone": "254712345678",
    "amount": 1000
  }
  ```

### 4.3 Stripe Payment Intent
- **URL**: `POST /api/v1/checkout/stripe/create-intent`
- **Payload**: `{ "course_id": 1, "amount": 100 }`

### 4.4 Submit Offline / Bank Payment Proof
- **URL**: `POST /api/v1/payments/proof` (multipart/form-data)

---

## 🎼 5. Instructor Masterclass Management

All endpoints require `role:instructor` or `role:admin`:

- **Course Management**: `apiResource('instructor/courses', InstructorCourseController)`
- **Add Lesson**: `POST /api/v1/instructor/courses/{course_id}/lessons`
- **Add Assignment**: `POST /api/v1/instructor/courses/{course_id}/assignments`
- **Grade Submission**: `POST /api/v1/instructor/submissions/{submission_id}/grade`
- **Quizzes & Questions**: `POST /api/v1/instructor/courses/{course_id}/quizzes`, `POST /api/v1/instructor/quizzes/{quiz_id}/questions`
- **Schedule Live Session**: `POST /api/v1/instructor/courses/{course_id}/sessions`

---

## 🛠️ 6. Administrator System APIs

All endpoints require `role:admin`:

- **Manage Users**: `apiResource('admin/users', AdminUserController)`
- **Manage Instruments**: `apiResource('admin/instruments', AdminInstrumentController)`
- **Payment Approvals**:
  - `POST /api/v1/admin/payments/record`
  - `POST /api/v1/admin/payments/{payment_id}/approve`
  - `POST /api/v1/admin/payments/{payment_id}/reject`
- **System Settings**: `GET /api/v1/admin/settings`, `POST /api/v1/admin/settings`
- **Blog & Comments Moderation**: `apiResource('admin/blogs', AdminBlogController)`
