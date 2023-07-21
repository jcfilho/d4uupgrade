<?php

namespace Daytours\ErrorLogs\Model;

use Daytours\ErrorLogs\Api\ErrorLogRepositoryInterface;
use Daytours\ErrorLogs\Model\ErrorLogFactory;

/**
 * Factory class for @see \Daytours\ErrorLogs\Model\ErrorLog
 */
class ErrorLogRepository implements ErrorLogRepositoryInterface
{
    /**
     * @var ErrorLogFactory
     */
    private $errorLogFactory;

    public function __construct(ErrorLogFactory $errorLogFactory)
    {
        $this->errorLogFactory = $errorLogFactory;
    }

    public function getList()
    {
        return $this->errorLogFactory->create()->getCollection()->getData();
    }

    public function recordError($data)
    {
        var_dump($data);
        if (
            $data["moduleName"] != null &&
            $data["message"] != null &&
            $data["date"] != null &&
            $data["location"] != null &&
            $data["moreDetails"] != null
        ) {

            $model = $this->errorLogFactory->create();
            $model->addData([
                "moduleName" => $data["moduleName"],
                "message" => $data["message"],
                "date" => $data["date"],
                "location" => $data["location"],
                "moreDetails" => $data["moreDetails"]
            ]);
            $saveData = $model->save();
            if ($saveData) {
                echo "Guardado exitoso";
                return;
            }
        } else {
            echo "Faltan campos";
            return;
        }
        echo "Error al guardar";
        return;
    }
}
