<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Database\Seeder;

class CertificateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hanya memberikan sertifikat kepada student yang telah menyelesaikan kursus
        $completedEnrollments = Enrollment::where('status', 'completed')->get();

        foreach ($completedEnrollments as $enrollment) {
            Certificate::create([
                'user_id' => $enrollment->user_id,
                'course_id' => $enrollment->course_id,
                'certificate_number' => 'CERT-' . strtoupper(uniqid()),
                'issued_at' => now()->subDays(rand(1, 60)),
            ]);
        }
    }
}
