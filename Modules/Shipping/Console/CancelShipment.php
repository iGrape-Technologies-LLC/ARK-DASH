<?php

namespace Modules\Shipping\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\Shipping\Carriers\AndreaniCarrier;

class CancelShipment extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'shipping:cancelshipment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancels shipping.';

    private $carriers = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AndreaniCarrier $andreani)
    {
        parent::__construct();

        $this->carriers[$andreani->getId()] = $andreani;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach($this->carriers as $carrier) {
            $carrier->cancel('310000011451964');
        }
    }
}
