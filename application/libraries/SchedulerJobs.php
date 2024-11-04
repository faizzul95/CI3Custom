<?php

class SchedulerJobs
{
    function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->load->model("SystemQueueJob_model");
    }

    /**
     * Get a undo job from database table
     *
     * @return array
     */
    public function getJob()
    {
        return $this->CI->SystemQueueJob_model->whereIn('status', [1, 2, 4])->where('attempt', '<', 10)->fetch();
    }

    /**
     * Process then update a job
     *
     * @param array $job
     * @return boolean
     */
    public function processJob($job)
    {
        $status = $job['status'];
        $attempt = $job['attempt'];
        $message = NULL;

        $data = json_decode($job['payload'], true);

        // Check if payload is not empty
        if (!empty($data)) {

            // update job record to running before process
            $this->CI->SystemQueueJob_model->patch(['attempt' => $attempt, 'status' => 2, 'message' => $message], $job['id']);

            $response = NULL;

            // Check if job is email
            if ($job['type'] == 'email') {
                $response = $this->mailJob($data);
            }

            if (!empty($response)) {
                $status = $response['status'];
                $message = $response['message'];
            }
        } else {
            $status = 4; // failed
            $message = 'payload is empty';
        }

        // Update job record to complete / failed after finishing process
        $this->CI->SystemQueueJob_model->patch(['attempt' => $attempt + 1, 'status' => $status, 'message' => $message], $job['id']);
    }

    private function mailJob($data)
    {
        $recipientData = [
            'recipient_name' => $data['name'],
            'recipient_email' => $data['to'],
            'recipient_cc' => $data['cc'],
            'recipient_bcc' => $data['bcc'],
        ];

        $sentEmail = sentMail($recipientData, $data['subject'], $data['body'], $data['attachment']);

        if ($sentEmail['success']) {
            return ['status' => 3, 'message' => $sentEmail['message']];
        } else {
            return ['status' => 4, 'message' => 'Failed to sent at ' . timestamp('d/m/Y h:i A') . ', ' . $sentEmail['message']];
        }
    }
}
