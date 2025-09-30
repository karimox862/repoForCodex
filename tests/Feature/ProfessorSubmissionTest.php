<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\Professor;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfessorSubmissionTest extends TestCase
{
    use RefreshDatabase;

    private User $professorUser;
    private Module $module;
    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->professorUser = User::factory()->create([
            'role' => User::ROLE_PROFESSOR,
        ]);

        $professor = Professor::create([
            'user_id' => $this->professorUser->id,
            'department' => 'Science',
        ]);

        $this->module = Module::create([
            'code' => 'SCI101',
            'title' => 'Science Basics',
            'professor_id' => $professor->id,
        ]);

        $this->student = Student::create([
            'first_name' => 'Student',
            'last_name' => 'One',
            'apogee_code' => 'APO001',
            'birth_date' => '2001-05-15',
            'email' => 'student1@example.com',
        ]);

        $this->module->students()->attach($this->student);
    }

    public function test_professor_can_submit_numeric_grade(): void
    {
        $this->actingAs($this->professorUser)
            ->post(route('professor.modules.students.mark', [$this->module, $this->student]), [
                'grade' => '18.5',
            ])
            ->assertRedirect(route('professor.modules.show', $this->module));

        $this->assertDatabaseHas('marks', [
            'module_id' => $this->module->id,
            'student_id' => $this->student->id,
            'grade' => '18.5',
        ]);
    }

    public function test_professor_can_submit_abi_grade(): void
    {
        $this->actingAs($this->professorUser)
            ->post(route('professor.modules.students.mark', [$this->module, $this->student]), [
                'grade' => 'abi',
            ])
            ->assertRedirect(route('professor.modules.show', $this->module));

        $this->assertDatabaseHas('marks', [
            'module_id' => $this->module->id,
            'student_id' => $this->student->id,
            'grade' => 'ABI',
        ]);
    }

    public function test_professor_cannot_submit_invalid_grade(): void
    {
        $response = $this->actingAs($this->professorUser)
            ->from(route('professor.modules.show', $this->module))
            ->post(route('professor.modules.students.mark', [$this->module, $this->student]), [
                'grade' => 'Pass',
            ]);

        $response->assertRedirect(route('professor.modules.show', $this->module));
        $response->assertSessionHasErrors('grade');

        $this->assertDatabaseCount('marks', 0);
    }
}
