<?php

namespace Modules\Shipping\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Modules\Shipping\Http\Controllers\ShippingController;

class UpdateShipments extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'shipping:updateshipments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates shipment status from carriers API';

    private $shippingController;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ShippingController $shipping)
    {
        parent::__construct();

        $this->shippingController = $shipping;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->shippingController->updateShipmentsStatus();
    }
}
