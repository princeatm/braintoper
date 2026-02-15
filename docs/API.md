# BrainToper API Documentation

## Authentication

### Login Endpoint
**POST** `/auth/login`

```json
{
  "login_code": "STU-12-3456",
  "pin": "1234"
}
```

Response (success):
```json
{
  "success": true,
  "redirect": "/dashboard/student"
}
```

### Check Session
**POST** `/auth/check-session`

Response:
```json
{
  "authenticated": true,
  "user_id": 1,
  "role": "student"
}
```

## Exam Endpoints

### Start Exam
**POST** `/exam/start`

Parameters:
- `exam_code` (string): The exam code

Response:
```json
{
  "success": true,
  "attempt_id": 1,
  "exam": { ... },
  "questions": [ ... ],
  "duration_seconds": 3600
}
```

### Save Answer
**POST** `/exam/save-answer`

Parameters:
- `attempt_id` (int)
- `question_id` (int)
- `option_id` (int, optional)
- `is_skipped` (boolean)

### Track User Action
**POST** `/exam/track-action`

Parameters:
- `attempt_id` (int)
- `action` (string): 'tab_switch' or 'focus_lost'

### Submit Exam
**POST** `/exam/submit`

Parameters:
- `attempt_id` (int)

### Auto-Submit Exam
**POST** `/exam/auto-submit`

Parameters:
- `attempt_id` (int)
- `reason` (string)

## Student Dashboard

### Get Exam Result
**GET** `/api/student/exam-result?attempt_id={id}`

Response:
```json
{
  "success": true,
  "result": {
    "id": 1,
    "exam_attempt_id": 1,
    "obtained_marks": 80,
    "total_marks": 100,
    "percentage": 80.00,
    "grade": "A"
  }
}
```

## Teacher Endpoints

### Create Exam
**POST** `/exam/create`

Parameters:
- `subject_id` (int)
- `title` (string)
- `description` (string)
- `exam_code` (string, optional)
- `class_id` (int)
- `grade_id` (int)
- `arm_id` (int)
- `duration_minutes` (int)
- `total_marks` (int)
- `passing_marks` (int)

### Get Leaderboard
**GET** `/api/teacher/leaderboard?exam_id={id}&academic_group_id={id}`

Response:
```json
{
  "success": true,
  "leaderboard": [
    {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "obtained_marks": 90,
      "percentage": 90.00,
      "grade": "A"
    }
  ]
}
```

## Admin Endpoints

### Get All Students
**GET** `/api/admin/students`

### Get All Teachers
**GET** `/api/admin/teachers`

## WebSocket Events

### Connection
```javascript
const ws = new WebSocket('ws://localhost:8080?type=student&id=1&exam_id=1');

ws.onmessage = (event) => {
  const data = JSON.parse(event.data);
  
  if (data.type === 'exam_progress') {
    // Handle exam progress update
  } else if (data.type === 'leaderboard_update') {
    // Handle leaderboard update
  }
};
```

### Broadcasting Exam Progress

Sockets should broadcast:
```json
{
  "type": "exam_progress",
  "studentId": 1,
  "examId": 1,
  "questionsCompleted": 5,
  "totalQuestions": 10
}
```

## Error Responses

Common error responses return:
```json
{
  "error": "Description of error"
}
```

HTTP Status Codes:
- `200`: Success
- `400`: Bad Request
- `403`: Forbidden
- `404`: Not Found
- `429`: Too Many Requests
- `500`: Internal Server Error
