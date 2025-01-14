<?php

use PHPUnit\Framework\TestCase;

class QueueTest extends TestCase
{

    /**
     * @covers MC4WP_Queue::all
     */
    public function test_all()
    {
        $queue = new MC4WP_Queue('option');
        self::assertEquals($queue->all(), array());

        $queue->put('one');
        $queue->put('two');
        self::assertCount(2, $queue->all());
    }

    /**
     * @covers MC4WP_Queue::put
     */
    public function test_put()
    {
        $queue = new MC4WP_Queue('option');
        $data = array( 'sample' => 'data' );
        $queue->put($data);

        $job = $queue->get();
        self::assertInstanceOf('MC4WP_Queue_Job', $job);
        self::assertEquals($job->data, $data);

        // calling get again should return same job
        self::assertTrue($job === $queue->get());

        // job should be added at the end, so still same instance
        $added = $queue->put('two');
        self::assertTrue($added);
        self::assertTrue($job === $queue->get());

        // add same job again, should not be added
        $added = $queue->put('two');
        self::assertFalse($added);
    }

    /**
     * @covers MC4WP_Queue::delete
     */
    public function test_delete()
    {
        $queue = new MC4WP_Queue('option');
        $queue->put(array( 'sample' => 'data' ));

        // get job then delete it from queue
        $job = $queue->get();
        $queue->delete($job);

        // queue should be empty now
        $job_2 = $queue->get();
        self::assertEmpty($job_2);
    }

    /**
     * @covers MC4WP_Queue::reset
     */
    public function test_reset()
    {
        $queue = new MC4WP_Queue('option');
        $queue->put(array( 'sample' => 'data' ));
        $queue->reset();

        self::assertEmpty($queue->all());
        self::assertEmpty($queue->get());
    }

    /**
     * @covers MC4WP_Queue::save
     */
    public function test_save()
    {
        $queue = new MC4WP_Queue('option');

        // nothing to save
        self::assertFalse($queue->save());

        // add something, then save
        $queue->put(array( 'key' => 'value' ));
        self::assertTrue($queue->save());
    }
}
