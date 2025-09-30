<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('apogee_code')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('apogee_code');
            $table->string('first_name')->nullable()->after('last_name');
            $table->date('birth_date')->nullable()->after('first_name');
        });

        $existing = DB::table('students')->select('id', 'name', 'registration_number', 'email')->get();

        foreach ($existing as $student) {
            $fullName = trim((string) $student->name);
            $firstName = null;
            $lastName = null;

            if ($fullName !== '') {
                $parts = preg_split('/\s+/', $fullName, -1, PREG_SPLIT_NO_EMPTY);

                if (! empty($parts)) {
                    $firstName = array_shift($parts);
                    if (! empty($parts)) {
                        $lastName = implode(' ', $parts);
                    }
                }
            }

            if ($firstName === null && $student->registration_number) {
                $firstName = $student->registration_number;
            }

            DB::table('students')
                ->where('id', $student->id)
                ->update([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'apogee_code' => $student->registration_number,
                    'name' => trim(implode(' ', array_filter([$firstName, $lastName]))),
                ]);
        }

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_registration_number_unique');
            $table->dropColumn('registration_number');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->unique('apogee_code');
        });

        $emails = $existing->pluck('email', 'id');

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_email_unique');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('first_name');
        });

        foreach ($emails as $id => $email) {
            DB::table('students')->where('id', $id)->update(['email' => $email]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $current = DB::table('students')->select('id', 'apogee_code', 'first_name', 'last_name', 'email', 'name')->get();

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_apogee_code_unique');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('registration_number')->nullable()->after('name');
        });

        foreach ($current as $student) {
            DB::table('students')
                ->where('id', $student->id)
                ->update([
                    'registration_number' => $student->apogee_code,
                    'name' => $student->name ?: trim(implode(' ', array_filter([$student->first_name, $student->last_name]))),
                ]);
        }

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_email_unique');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['apogee_code', 'last_name', 'first_name', 'birth_date', 'email']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('email')->unique()->after('name');
        });

        foreach ($current as $student) {
            DB::table('students')->where('id', $student->id)->update(['email' => $student->email ?? '']);
        }

        Schema::table('students', function (Blueprint $table) {
            $table->unique('registration_number');
        });
    }
};
