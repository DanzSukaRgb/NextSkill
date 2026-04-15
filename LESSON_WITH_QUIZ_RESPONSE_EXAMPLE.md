# GET /courses/{courseId}/lessons - Response Example

## Response Format (Detailed)

```json
{
    "success": true,
    "message": "Daftar lesson",
    "data": {
        "data": [
            {
                "id": "550e8400-e29b-41d4-a716-446655440001",
                "course_id": "550e8400-e29b-41d4-a716-446655440000",
                "title": "Pengenalan Database",
                "content": "Pelajari konsep dasar database relasional dan bagaimana cara kerjanya.",
                "vidio_url": "https://www.youtube.com/embed/abc123",
                "file_path": "https://example.com/storage/lessons/database-intro.pdf",
                "order_number": 1,
                "duration_in_minutes": 35,
                "is_preview": true,
                "quizzes": [
                    {
                        "id": 101,
                        "title": "Quiz: Konsep Dasar Database",
                        "description": "Test pemahaman Anda tentang database",
                        "type": "mcq",
                        "time_limit": 15,
                        "minimum_score": 70,
                        "total_questions": 5
                    },
                    {
                        "id": 102,
                        "title": "Matching: Istilah Database",
                        "description": "Cocokkan istilah dengan definisinya",
                        "type": "matching",
                        "time_limit": 10,
                        "minimum_score": 60,
                        "total_questions": 8
                    }
                ],
                "created_at": "2026-04-10 08:30:00",
                "updated_at": "2026-04-14 15:45:00"
            },
            {
                "id": "550e8400-e29b-41d4-a716-446655440002",
                "course_id": "550e8400-e29b-41d4-a716-446655440000",
                "title": "Query SQL Dasar",
                "content": "Pelajari cara membuat query SQL untuk mengambil dan memanipulasi data.",
                "vidio_url": "https://www.youtube.com/embed/def456",
                "file_path": "https://example.com/storage/lessons/sql-basics.pdf",
                "order_number": 2,
                "duration_in_minutes": 45,
                "is_preview": false,
                "quizzes": [
                    {
                        "id": 103,
                        "title": "Quiz: SQL SELECT Statements",
                        "description": "Test pengetahuan Anda tentang SELECT query",
                        "type": "mcq",
                        "time_limit": 20,
                        "minimum_score": 75,
                        "total_questions": 10
                    }
                ],
                "created_at": "2026-04-11 09:15:00",
                "updated_at": "2026-04-13 10:20:00"
            },
            {
                "id": "550e8400-e29b-41d4-a716-446655440003",
                "course_id": "550e8400-e29b-41d4-a716-446655440000",
                "title": "JOIN dan Subqueries",
                "content": "Pelajari cara menggabungkan tabel menggunakan JOIN dan Subqueries.",
                "vidio_url": "https://www.youtube.com/embed/ghi789",
                "file_path": "https://example.com/storage/lessons/joins-subqueries.pdf",
                "order_number": 3,
                "duration_in_minutes": 50,
                "is_preview": false,
                "quizzes": [],
                "created_at": "2026-04-12 11:00:00",
                "updated_at": "2026-04-12 11:00:00"
            }
        ],
        "unrelated_quizzes": [
            {
                "id": 201,
                "title": "Mid-Term Assessment",
                "description": "Evaluasi kemampuan Anda di tengah kursus",
                "type": "mcq",
                "time_limit": 60,
                "minimum_score": 80,
                "total_questions": 25
            },
            {
                "id": 202,
                "title": "Database Design Challenge",
                "description": "Quiz menantang tentang desain database",
                "type": "mcq",
                "time_limit": 45,
                "minimum_score": 75,
                "total_questions": 15
            }
        ],
        "pagination": {
            "total": 3,
            "per_page": 10,
            "current_page": 1,
            "last_page": 1,
            "from": 1,
            "to": 3,
            "path": "/api/courses/550e8400-e29b-41d4-a716-446655440000/lessons"
        }
    }
}
```

## Penjelasan Field:

### Lesson Fields:

- `id` - UUID unik lesson
- `course_id` - UUID course yang lesson ini terdaftar
- `title` - Judul lesson
- `content` - Deskripsi konten lesson
- `vidio_url` - URL video YouTube
- `file_path` - URL file materi (PDF, PPT, dll)
- `order_number` - Urutan lesson dalam course (1, 2, 3, dst)
- `duration_in_minutes` - Durasi video dalam menit
- `is_preview` - Boolean, apakah lesson bisa di-preview gratis
- `quizzes` - Array of quizzes yang terhubung dengan lesson ini
- `created_at` - Waktu dibuat
- `updated_at` - Waktu update terakhir

### Quiz Fields (per lesson):

- `id` - ID quiz
- `title` - Judul quiz
- `description` - Deskripsi quiz
- `type` - Tipe quiz: `mcq` (multiple choice) atau `matching`
- `time_limit` - Batas waktu dalam menit
- `minimum_score` - Skor minimum untuk lulus (0-100)
- `total_questions` - Total jumlah soal

### Unrelated Quizzes:

- Quiz yang `lesson_id` nya NULL
- Biasanya: mid-term assessment, final test, general quizzes
- Ditampilkan di akhir (setelah semua lessons)

### Pagination:

- `total` - Total lessons
- `per_page` - Lessons per halaman
- `current_page` - Halaman saat ini
- `last_page` - Halaman terakhir
- `from` - Nomor urut pertama di halaman ini
- `to` - Nomor urut terakhir di halaman ini

## Query Parameters:

```
GET /courses/{courseId}/lessons?page=1&perPage=10&search=Query
```

- `page` - Nomor halaman (default: 1)
- `perPage` - Jumlah per halaman (default: 10)
- `search` - Pencarian berdasarkan judul lesson
