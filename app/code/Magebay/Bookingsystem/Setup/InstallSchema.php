<?php
 
namespace Magebay\Bookingsystem\Setup;
 
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
 
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
 
        // Get booking_systems  table
        $bookingSystems = $installer->getTable('booking_systems');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($bookingSystems) != true) {
            // Create booking_systems table
            $tableBookingSystems = $installer->getConnection()
                ->newTable($bookingSystems)
                ->addColumn(
                    'booking_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'Booking Id'
                )
                ->addColumn(
                    'booking_type',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Booking Type'
                )
				 ->addColumn(
                    'booking_service_start',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Service Start'
                )
                ->addColumn(
                    'booking_service_end',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Service End'
                )
                ->addColumn(
                    'booking_product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Product Id'
                )
				->addColumn(
                    'booking_time',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Booking Time'
                )
				->addColumn(
                    'booking_min_days',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Min Days'
                )
				->addColumn(
                    'booking_max_days',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Max Days' 
                )
				->addColumn(
                    'booking_min_hours',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Min Hours' 
                )
				->addColumn(
                    'booking_max_hours',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Max Hours' 
                )
				->addColumn(
                    'booking_fee_night',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Fee Night' 
                )->addColumn(
                    'booking_time_slot',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Booking Time Slot' 
                )
				->addColumn(
                    'booking_time_buffer',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Booking Time Buffer' 
                )
				->addColumn(
                    'booking_show_finish',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Show Time Finish' 
                )
				->addColumn(
                    'booking_show_qty',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Show Qty Available' 
                ) 	
				->addColumn(
                    'booking_phone',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Booking Phone'
                )
				->addColumn(
                    'booking_email',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Booking Email'
                )
				->addColumn(
                    'booking_city',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Booking City'
                )
				->addColumn(
                    'booking_zipcode',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Booking Zip code'
                )
				->addColumn(
                    'booking_country',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Booking Country'
                )
				->addColumn(
                    'booking_state',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Booking State'
                )
				->addColumn(
                    'booking_state_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'default' => 0],
                    'Booking State Id'
                )
				->addColumn(
                    'booking_address',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Booking Auto Address'
                )
				->addColumn(
                    'booking_lat',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true, 'default' => 0],
                    'Booking Auto Address'
                )
				->addColumn(
                    'booking_lon',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true, 'default' => 0],
                    'Booking Auto Address'
                )
				->addColumn(
                    'auto_address',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Booking Auto Address'
                )
                ->setComment('Main Table Booking systems')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($tableBookingSystems);
        }
		//table facilities
		$facilities = $installer->getTable('booking_facilities');
		if($installer->getConnection()->isTableExists($facilities) != true) {
			 $tableFacilities = $installer->getConnection()
                ->newTable($facilities)
                ->addColumn(
                    'facility_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'Facility Id'
                )
                ->addColumn(
                    'facility_status',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Status'
                )
				->addColumn(
                    'facility_title',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Title'
                )
				->addColumn(
                    'facility_image',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'Title'
                )
				->addColumn(
                    'facility_icon_class',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'icon_class'
                )
				->addColumn(
                    'facility_description',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'font_icon'
                )
				->addColumn(
                    'facility_position',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'font_icon'
                )
				->addColumn(
                    'facility_booking_type',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'font_icon'
                )
				->addColumn(
                    'facility_booking_ids',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'font_icon'
                )
				->addColumn(
                    'facility_title_transalte',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'font_icon'
                )
				->addColumn(
                    'facility_des_translate',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'font_icon'
                )
				->setComment('Facilities Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($tableFacilities);
		}
		// table room
		$rooms = $installer->getTable('booking_rooms');
		if($installer->getConnection()->isTableExists($rooms) != true) {
			 $tableRooms = $installer->getConnection()
                ->newTable($rooms)
                ->addColumn(
                    'room_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'room Id'
                )
                ->addColumn(
                    'room_status',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Status'
                )
				->addColumn(
                    'room_type',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Room Type'
                )
				->addColumn(
                    'room_max_adults',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Room max_adults'
                )
				->addColumn(
                    'room_max_children',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Room max_children'
                )
				->addColumn(
                    'room_position',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Room position'
                )
				->addColumn(
                    'room_booking_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Room booking_id'
                )
				->addColumn(
                    'room_minimum_day',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Room booking_id'
                )
				->addColumn(
                    'room_maximum_day',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Room booking_id'
                )
				->addColumn(
                    'room_description',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'description'
                )
				->addColumn(
                    'room_des_translate',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'description'
                )
				->setComment('Rooms Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($tableRooms);
		}
		// table room type 
		$roomtypes = $installer->getTable('booking_roomtypes');
		if($installer->getConnection()->isTableExists($roomtypes) != true) {
			 $tableRoomtypes = $installer->getConnection()
                ->newTable($roomtypes)
                ->addColumn(
                    'roomtype_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'room type Id'
                )
                ->addColumn(
                    'roomtype_status',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Status'
                )
				->addColumn(
                    'roomtype_title',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'roomtype title'
                )
				->addColumn(
                    'roomtype_position',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'roomtype position'
                )
				->addColumn(
                    'roomtype_title_transalte',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'roomtype title transalte'
                )
				->setComment('roomtype Table')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($tableRoomtypes);
		}
		//table calendars
		$calendars = $installer->getTable('booking_calendars');
		if($installer->getConnection()->isTableExists($calendars) != true) {
			 $tableCalendar = $installer->getConnection()
                ->newTable($calendars)
                ->addColumn(
                    'calendar_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'calendar Id'
                )
                ->addColumn(
                    'calendar_startdate',
                    Table::TYPE_DATE,
                    null,
                    ['nullable' => true],
                    'Start date'
                )
				->addColumn(
                    'calendar_enddate',
                    Table::TYPE_DATE,
                    null,
                    ['nullable' => true],
                    'Start date'
                )
				->addColumn(
                    'calendar_qty',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Calendar Qty'
                )
				->addColumn(
                    'calendar_status',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Calendar Status'
                )
				->addColumn(
                    'calendar_price',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true, 'default' => 0],
                    'Calendar Price'
                )
				->addColumn(
                    'calendar_promo',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => true, 'default' => 0],
                    'Calendar Price'
                )
				->addColumn(
                    'calendar_booking_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Booking Id'
                )
				->addColumn(
                    'calendar_default_value',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Default Value'
                )
				->addColumn(
                    'calendar_booking_type',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Default booking_type'
                )
				->setComment('Table Calendar')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($tableCalendar);
		}
		//table orders
		$bookingOrders = $installer->getTable('booking_orders');
		if($installer->getConnection()->isTableExists($bookingOrders) != true) {
			 $tableBookingOrders = $installer->getConnection()
                ->newTable($bookingOrders)
                ->addColumn(
                    'bkorder_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'calendar Id'
                )
				->addColumn(
                    'bkorder_check_in',
                    Table::TYPE_DATE,
                    null,
                    ['nullable' => false],
                    'Check In'
                )
				->addColumn(
                    'bkorder_check_out',
                    Table::TYPE_DATE,
                    null,
                    ['nullable' => false],
                    'Check Out'
                )
				->addColumn(
                    'bkorder_customer',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true,'default' => ''],
                    'Infor Customers'
                )
				->addColumn(
                    'bkorder_qty',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'order qty'
                )
				->addColumn(
                    'bkorder_booking_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'bkorder booking_id'
                )
				->addColumn(
                    'bkorder_room_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'bkorder room_id'
                )
				->addColumn(
                    'bkorder_room_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'bkorder room_id'
                )
				->addColumn(
                    'bkorder_order_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'bkorder order id'
                )
				->addColumn(
                    'bkorder_service_start',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'bkorder service start'
                )
				->addColumn(
                    'bkorder_service_end',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true],
                    'bkorder service end'
                )
				->addColumn(
                    'bkorder_total_days',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'bkorder total_days'
                )
				->addColumn(
                    'bkorder_total_hours',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'bkorder total_hours'
                )
				->addColumn(
                    'bkorder_qt_item_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'bkorder qt_item_id'
                )
				->addColumn(
                    'bkorder_quantity_interval',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'bkorder quantity_interval'
                )
				->addColumn(
                    'bkorder_interval_time',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'bkorder interval_time'
                )
				->setComment('Table Bk order')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($tableBookingOrders);
		}
		//table booking_intervalhours
		$intervalhours = $installer->getTable('booking_intervalhours');
		if($installer->getConnection()->isTableExists($intervalhours) != true) {
			 $tableIntervalhours = $installer->getConnection()
                ->newTable($intervalhours)
                ->addColumn(
                    'intervalhours_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'intervalhours Id'
                )
				->addColumn(
                    'intervalhours_booking_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'booking_id'
                )
				->addColumn(
                    'intervalhours_quantity',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'quantity'
                )
				->addColumn(
                    'intervalhours_booking_time',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'booking_time'
                )
				->addColumn(
                    'intervalhours_check_in',
                    Table::TYPE_DATE,
                    null,
                    ['nullable' => true],
                    'check_in'
                )
				->addColumn(
                    'intervalhours_check_out',
                    Table::TYPE_DATE,
                    null,
                    ['nullable' => true],
                    'check_in'
                )
				->setComment('Table intervalhours ')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($tableIntervalhours);
		}
		//table booking_options
		$options = $installer->getTable('booking_options');
		if($installer->getConnection()->isTableExists($options) != true) {
			 $tableOptions = $installer->getConnection()
                ->newTable($options)
                ->addColumn(
                    'option_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'option Id'
                )
				->addColumn(
                    'option_title',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'title'
                )
				->addColumn(
                    'option_type',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'optoin_type'
                )
				->addColumn(
                    'option_required',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'optoin required'
                )
				->addColumn(
                    'option_sort',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'option sort'
                )
				->addColumn(
                    'option_price',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'price'
                )
				->addColumn(
                    'option_max_number',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'max_number'
                )
				->addColumn(
                    'option_booking_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'booking_id'
                )
				->addColumn(
                    'option_booking_type',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'booking_type'
                )
				->addColumn(
                    'option_title_translate',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'title'
                )
				->setComment('Table options ')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($tableOptions);
		}
		//table discount
		$discounts = $installer->getTable('booking_discounts');
		if($installer->getConnection()->isTableExists($discounts) != true) {
			 $tableDiscounts = $installer->getConnection()
                ->newTable($discounts)
                ->addColumn(
                    'discount_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'discount Id'
                )
				->addColumn(
                    'discount_type',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' =>0],
                    'discount_type'
                )
				->addColumn(
                    'discount_period',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'discount period'
                )
				->addColumn(
                    'discount_amount',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'discount amount'
                )
				->addColumn(
                    'discount_max_items',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'discount max_items'
                )
				->addColumn(
                    'discount_booking_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'discount booking_id'
                )
				->addColumn(
                    'discount_booking_type',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' =>''],
                    'discount booking_type'
                )
				->addColumn(
                    'discount_amount_type',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'discount amount_type'
                )
				->addColumn(
                    'discount_priority',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'discount priority'
                )
				->setComment('Table Discounts ')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($tableDiscounts);
		}
		//table booking_option_dropdonw
		$optionsDropdown = $installer->getTable('booking_option_dropdown');
		if($installer->getConnection()->isTableExists($optionsDropdown) != true) {
			 $tableOptionsDropdown = $installer->getConnection()
                ->newTable($optionsDropdown)
                ->addColumn(
                    'dropdown_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'dropdown Id'
                )
				->addColumn(
                    'dropdown_title',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'title'
                )
				->addColumn(
                    'dropdown_price',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'optoin_type'
                )
				->addColumn(
                    'dropdown_option_id',
                    Table::TYPE_SMALLINT,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'optoin required'
                )
				->addColumn(
                    'dropdown_title_translate',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => true, 'default' => ''],
                    'title'
                )
				->setComment('Table options ')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($tableOptionsDropdown);
		}
		//table booking_bookingimages
		$bookingimages = $installer->getTable('booking_bookingimages');
		if($installer->getConnection()->isTableExists($bookingimages) != true) {
			 $tableBookingimages = $installer->getConnection()
                ->newTable($bookingimages)
                ->addColumn(
                    'bkimage_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'dropdown Id'
                )
				->addColumn(
                    'bkimage_path',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'image path'
                )
				->addColumn(
                    'bkimage_title',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'title'
                )
				->addColumn(
                    'bkimage_type',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'image required'
                )
				->addColumn(
                    'bkimage_data_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'image bkimage_data_id'
                )
				
				->setComment('Table options ')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($tableBookingimages);
		}
        $installer->endSetup();
    }
}
 