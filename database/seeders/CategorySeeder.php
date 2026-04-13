<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Web Development',
                'icon' => '🌐',
                'description' => 'Pelajari pengembangan website modern dengan HTML, CSS, JavaScript, dan framework populer',
            ],
            [
                'name' => 'Mobile Development',
                'icon' => '📱',
                'description' => 'Kuasai pengembangan aplikasi mobile untuk iOS dan Android',
            ],
            [
                'name' => 'Data Science',
                'icon' => '📊',
                'description' => 'Analisis data dan machine learning dengan Python dan tools populer',
            ],
            [
                'name' => 'Backend Development',
                'icon' => '⚙️',
                'description' => 'Pelajari PHP, Node.js, Python untuk pengembangan backend yang robust',
            ],
            [
                'name' => 'Database Management',
                'icon' => '🗄️',
                'description' => 'Penguasaan SQL, MongoDB, Firebase dan sistem database lainnya',
            ],
            [
                'name' => 'DevOps & Cloud',
                'icon' => '☁️',
                'description' => 'Docker, Kubernetes, AWS, Google Cloud dan infrastruktur cloud',
            ],
            [
                'name' => 'UI/UX Design',
                'icon' => '🎨',
                'description' => 'Desain interface dan user experience yang menarik',
            ],
            [
                'name' => 'AI & Machine Learning',
                'icon' => '🤖',
                'description' => 'Artificial Intelligence, Deep Learning dan implementasi AI praktis',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
