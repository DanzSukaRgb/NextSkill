<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mentor = User::where('role', 'mentor')->first();
        $categories = Category::all();

        $coursesData = [
            [
                'category_id' => $categories->firstWhere('name', 'Web Development')->id,
                'title' => 'Mastering Laravel 11',
                'description' => 'Pelajari Laravel framework terbaru untuk membangun aplikasi web yang powerful dan scalable.',
                'level' => 'intermediate',
                'price' => 250000,
                'is_certificate' => true,
            ],
            [
                'category_id' => $categories->firstWhere('name', 'Web Development')->id,
                'title' => 'React.js dari Nol hingga Expert',
                'description' => 'Kuasai React untuk membangun UI yang interaktif dan dynamic dengan hooks dan Redux.',
                'level' => 'intermediate',
                'price' => 300000,
                'is_certificate' => true,
            ],
            [
                'category_id' => $categories->firstWhere('name', 'Backend Development')->id,
                'title' => 'Node.js & Express.js Complete Guide',
                'description' => 'Membangun REST API dan aplikasi backend dengan Node.js dan Express.js.',
                'level' => 'beginner',
                'price' => 200000,
                'is_certificate' => true,
            ],
            [
                'category_id' => $categories->firstWhere('name', 'Data Science')->id,
                'title' => 'Python untuk Data Science',
                'description' => 'Analisis data dan visualisasi dengan pandas, numpy, dan matplotlib.',
                'level' => 'intermediate',
                'price' => 280000,
                'is_certificate' => true,
            ],
            [
                'category_id' => $categories->firstWhere('name', 'Mobile Development')->id,
                'title' => 'Flutter Development Masterclass',
                'description' => 'Buat aplikasi mobile cross-platform dengan Flutter dan Dart.',
                'level' => 'beginner',
                'price' => 220000,
                'is_certificate' => true,
            ],
            [
                'category_id' => $categories->firstWhere('name', 'Database Management')->id,
                'title' => 'SQL Database Expert',
                'description' => 'Query optimization, indexing, dan database design patterns.',
                'level' => 'intermediate',
                'price' => 180000,
                'is_certificate' => true,
            ],
            [
                'category_id' => $categories->firstWhere('name', 'DevOps & Cloud')->id,
                'title' => 'Docker & Kubernetes untuk Production',
                'description' => 'Containerization dan orchestration untuk aplikasi production-ready.',
                'level' => 'advanced',
                'price' => 350000,
                'is_certificate' => true,
            ],
            [
                'category_id' => $categories->firstWhere('name', 'UI/UX Design')->id,
                'title' => 'Figma Design System Master',
                'description' => 'Membuat design system yang konsisten dengan Figma.',
                'level' => 'beginner',
                'price' => 150000,
                'is_certificate' => true,
            ],
            [
                'category_id' => $categories->firstWhere('name', 'Web Development')->id,
                'title' => 'Vue.js 3 Advanced Concepts',
                'description' => 'Compositional API, stores dengan Pinia, dan advanced patterns.',
                'level' => 'advanced',
                'price' => 320000,
                'is_certificate' => true,
            ],
            [
                'category_id' => $categories->firstWhere('name', 'AI & Machine Learning')->id,
                'title' => 'Deep Learning dengan TensorFlow',
                'description' => 'Neural networks, CNN, RNN dan aplikasi deep learning praktis.',
                'level' => 'advanced',
                'price' => 400000,
                'is_certificate' => true,
            ],
        ];

        foreach ($coursesData as $courseData) {
            Course::create([
                'id' => Str::uuid(),
                'category_id' => $courseData['category_id'],
                'user_id' => $mentor->id,
                'title' => $courseData['title'],
                'description' => $courseData['description'],
                'thumbnail' => 'https://via.placeholder.com/400x300?text=' . urlencode($courseData['title']),
                'level' => $courseData['level'],
                'price' => $courseData['price'],
                'status' => 'published',
                'is_certificate' => $courseData['is_certificate'],
            ]);
        }
    }
}
