<?php

namespace App\Repository;

use App\Model\Warehouse;

class WarehouseRepository extends AbstractRepository
{
    public function __construct($dbConnection)
    {
        parent::__construct($dbConnection);
    }

    /**
     * @return Warehouse[]
     */

    public function getAll()
    {
        $warehouses = [];
        $rows = $this->dbConnection->executeQuery("SELECT *
            FROM Warehouse WHERE id_user = $this->id_user");
        while ($row = $rows->fetch(\PDO::FETCH_ASSOC)) {
            $warehouse = new Warehouse($row['id'], $row['address'], $row['capacity'], $this->id_user);
            array_push($warehouses, $warehouse);
        }
        return $warehouses;
    }

    /**
     * @param int $id
     * @return Warehouse|null
     */
    private function getBy($str)
    {
        $rows = $this->dbConnection->executeQuery("
         SELECT *
            FROM final_work.Warehouse
            WHERE id_user = $this->id_user and ".$str
        );
        $row = $rows->fetch(\PDO::FETCH_ASSOC);
        return new Warehouse($row['id'], $row['address'], $row['capacity'], $row['id_user']);
    }

    public function getById($id)
    {
        return $this->getBy("id = $id");
    }

    public function getByAddress($address)
    {
        return $this->getBy("address = \"$address\"; ");
    }

    public function add(Warehouse $warehouse)
    {
        if($this->Check($warehouse)->getAddress() != null) return ["ERROR"=>"Адрес уже используется"];
        $this->dbConnection->executeQuery(
            'INSERT INTO final_work.Warehouse  (address, capacity, id_user) 
              values (?, ?, ?); ', [$warehouse->getAddress(), $warehouse->getCapacity(), $this->id_user]);
        $warehouse->setId( $this->dbConnection->lastInsertId());
        $warehouse->setUserId( $this->id_user);
        return $warehouse;

    }
    /**
     * @var Warehouse
     * @return bool|Warehouse[]
    */
    private function Check($warehouse)
    {
        $w = $this->getByAddress($warehouse->getAddress());
        return $w;
    }

    public function updateWarehouse($old_address, $new_address, $new_capacity)
    {
        $warehouse = $this->getByAddress($old_address);
        if($warehouse->getAddress() != null)
        {
            if($new_capacity != "" && $new_capacity != null){
                $this->dbConnection->executeQuery(
                    'update final_work.Warehouse set Warehouse.capacity = ? where Warehouse.id = ? and Warehouse.id_user = ?;',
                    [$new_capacity, $warehouse->getId(), $this->id_user]);
                $warehouse = $this->getByAddress($old_address);
            }

            if($new_address != "" && $new_address != null && $old_address != $new_address){
                if($this->getByAddress($new_address)->getAddress() != null )return ["ERROR"=>"Адрес уже используется"];
                $this->dbConnection->executeQuery(
                    'update final_work.Warehouse set address = ? where id = ? and id_user = ?;',
                    [$new_address, $warehouse->getId(), $this->id_user]);
                $warehouse = $this->getByAddress($new_address);
            }
            return $warehouse;
        }
        else {
            return ["ERROR"=>"Склад не найден"];
        }
    }

    public function deleteWarehouse($address)
    {
        $warehouse = $this->getByAddress($address);
        if($warehouse->getAddress()==null)return ["ERROR"=>"Склад не найден"];
        $this->dbConnection->executeQuery(
            'delete from final_work.Warehouse where address = ? and id_user = ?;',
            [$address, $this->id_user]);
        return $warehouse;
    }
}