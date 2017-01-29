<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\RRHH\Entities\Employee;
use Mail;

class CreateEmployeeEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $employee;
    protected $pssw;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Employee $employee, $pssw)
    {
        $this->employee = $employee;
        $this->pssw = $pssw;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $content = $this->pssw;
        Mail::raw("Su password es $this->pssw",function ($m) {
            $m->from("rociom@novatechnology.com.ec","Gilbert & Bolona");
            $m->to($this->employee->user->email, $this->employee->name);
            $m->subject("Creación Usuario G&B");
        });
    }
}
