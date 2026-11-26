<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCourseCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_can_be_created_viewed_updated_and_deleted(): void
    {
        $studentData = [
            'name' => 'Avery Student',
            'email' => 'avery@example.com',
            'phone' => '1234567890',
            'address' => '12 Main Street',
            'date_of_birth' => '2000-05-10',
        ];

        $this->post(route('students.store'), $studentData)
            ->assertRedirect(route('students.index'));

        $student = Student::firstOrFail();
        $this->assertDatabaseHas('students', ['email' => $studentData['email']]);
        $this->get(route('students.index'))->assertOk()->assertSee($studentData['name']);
        $this->get(route('students.create'))->assertOk();
        $this->get(route('students.show', $student))->assertOk()->assertSee($studentData['name']);
        $this->get(route('students.edit', $student))->assertOk();

        $this->put(route('students.update', $student), [
            ...$studentData,
            'name' => 'Avery Updated',
        ])->assertRedirect(route('students.index'));

        $this->assertDatabaseHas('students', ['id' => $student->id, 'name' => 'Avery Updated']);
        $this->delete(route('students.destroy', $student))
            ->assertRedirect(route('students.index'));

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_student_email_must_be_unique(): void
    {
        $student = Student::create([
            'name' => 'Existing Student',
            'email' => 'taken@example.com',
            'phone' => '1234567890',
            'address' => '12 Main Street',
            'date_of_birth' => '2000-05-10',
        ]);

        $this->from(route('students.create'))
            ->post(route('students.store'), [
                'name' => 'Another Student',
                'email' => $student->email,
                'phone' => '1234567890',
                'address' => '12 Main Street',
                'date_of_birth' => '2000-05-10',
            ])
            ->assertRedirect(route('students.create'))
            ->assertSessionHasErrors('email');
    }

    public function test_courses_can_be_created_viewed_updated_and_deleted(): void
    {
        $courseData = [
            'name' => 'Laravel Basics',
            'description' => 'An introduction to Laravel.',
            'duration' => 8,
            'fee' => '199.00',
            'difficulty' => 'Easy',
            'is_active' => '0',
        ];

        $this->post(route('courses.store'), $courseData)
            ->assertRedirect(route('courses.index'));

        $course = Course::firstOrFail();
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'is_active' => false]);
        $this->get(route('courses.index'))->assertOk()->assertSee($courseData['name']);
        $this->get(route('courses.create'))->assertOk();
        $this->get(route('courses.show', $course))->assertOk()->assertSee($courseData['name']);
        $this->get(route('courses.edit', $course))->assertOk();

        $this->put(route('courses.update', $course), [
            ...$courseData,
            'name' => 'Laravel Intermediate',
            'is_active' => '1',
        ])->assertRedirect(route('courses.index'));

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Laravel Intermediate',
            'is_active' => true,
        ]);
        $this->delete(route('courses.destroy', $course))
            ->assertRedirect(route('courses.index'));

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }
}
