<?php

namespace App\Models;

use App\Core\Model;

class Venda extends Model 
{
    public function query($sql, $params = []) 
    {
        // O $this->db que o Model (pai) criou no construtor
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}