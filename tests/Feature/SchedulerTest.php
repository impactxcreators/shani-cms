<?php

namespace Tests\Feature;

use App\Models\PushMessage;
use Illuminate\Console\Scheduling\CallbackEvent;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulerTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_task_needs_a_separate_process(): void
    {
        // Hostinger has no proc_open: Schedule::command() would silently never run
        foreach (app(Schedule::class)->events() as $event) {
            $this->assertInstanceOf(CallbackEvent::class, $event, 'Use Schedule::call() for: '.$event->getSummaryForDisplay());
        }
    }

    public function test_schedule_run_writes_heartbeat_and_sends_due_campaigns(): void
    {
        @unlink(storage_path('app/scheduler-heartbeat'));
        $due = PushMessage::create(['title' => 'Due', 'body' => 'B', 'scheduled_at' => now()->subMinute()]);

        $this->artisan('schedule:run')->assertSuccessful();

        $this->assertFileExists(storage_path('app/scheduler-heartbeat'));
        $this->assertNotNull($due->fresh()->sent_at);
    }
}
