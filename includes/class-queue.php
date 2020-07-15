<?php

/**
 * Class MC4WP_Queue
 *
 * @ignore
 */
class MC4WP_Queue {

	/**
	 * @var MC4WP_Queue_Job[]
	 */
	protected $jobs;

	/**
	 * @var string
	 */
	protected $option_name;

	/**
	 * @var bool
	 */
	protected $dirty = false;

	/**
	 * @var int
	 */
	const MAX_JOB_COUNT = 1000;

	/**
	 * MC4WP_Ecommerce_Queue constructor.
	 *
	 * @param string $option_name
	 */
	public function __construct( $option_name ) {
		$this->option_name = $option_name;

		register_shutdown_function( array( $this, 'save' ) );
	}

	/**
	 * Load jobs from option
	 */
	protected function load() {
		if ( ! is_null( $this->jobs ) ) {
			return;
		}

		$jobs = get_option( $this->option_name, array() );

		if ( ! is_array( $jobs ) ) {
			$jobs = array();
		} else {
			$valid_jobs = array();

			foreach ( $jobs as $i => $obj ) {
				// filter invalid data from array
				if ( ! is_object( $obj ) || empty( $obj->data ) ) {
					continue;
				}

				// make sure each job is instance of MC4WP_Queue_Job
				if ( $obj instanceof MC4WP_Queue_Job ) {
					$job = $obj;
				} else {
					$job     = new MC4WP_Queue_Job( $obj->data );
					$job->id = $obj->id;
				}

				$valid_jobs[] = $job;
			}

			$jobs = $valid_jobs;
		}

		$this->jobs = $jobs;
	}

	/**
	 * Get all jobs in the queue
	 *
	 * @return MC4WP_Queue_Job[] Array of jobs
	 */
	public function all() {
		$this->load();
		return $this->jobs;
	}

	/**
	 * Add job to queue
	 *
	 * @param mixed $data
	 * @return boolean
	 */
	public function put( $data ) {
		$this->load();

		// check if we already have a job with same data
		foreach ( $this->jobs as $job ) {
			if ( $job->data === $data ) {
				return false;
			}
		}

		// if we have more than MAX_JOB_COUNT jobs, remove first job item.
		// this protects against an ever-growing job list, but also potentially loses jobs if the queue is not processed soon enough.
		if ( count( $this->jobs ) > self::MAX_JOB_COUNT ) {
			array_shift( $this->jobs );
		}

		// add job to end of jobs array
		$job          = new MC4WP_Queue_Job( $data );
		$this->jobs[] = $job;
		$this->dirty  = true;
		return true;
	}

	/**
	 * Get all jobs in the queue
	 *
	 * @return MC4WP_Queue_Job|false
	 */
	public function get() {
		$this->load();

		// do we have jobs?
		if ( count( $this->jobs ) === 0 ) {
			return false;
		}

		// return first element
		return reset( $this->jobs );
	}

	/**
	 * @param MC4WP_Queue_Job $job
	 */
	public function delete( MC4WP_Queue_Job $job ) {
		$this->load();

		$index = array_search( $job, $this->jobs, true );

		// check for "false" here, as 0 is a valid index.
		if ( $index !== false ) {
			unset( $this->jobs[ $index ] );
			$this->jobs  = array_values( $this->jobs );
			$this->dirty = true;
		}
	}

	/**
	 * @param MC4WP_Queue_Job $job
	 */
	public function reschedule( MC4WP_Queue_Job $job ) {
		$this->load();

		// delete job from start of queue
		$this->delete( $job );

		// add job to end of queue
		$this->jobs[] = $job;
		$this->dirty  = true;
	}

	/**
	 * Reset queue
	 */
	public function reset() {
		$this->jobs  = array();
		$this->dirty = true;
	}

	/**
	 * Save the queue
	 */
	public function save() {
		if ( ! $this->dirty || is_null( $this->jobs ) ) {
			return false;
		}

		$success = update_option( $this->option_name, $this->jobs, false );

		if ( $success ) {
			$this->dirty = false;
		}

		return $success;
	}
}
