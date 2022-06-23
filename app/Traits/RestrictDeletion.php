<?php

namespace App\Traits;

use App\Exceptions\NotDeletableModel;

trait RestrictDeletion
{
  public function isDeletable()
  {
    return true;
  }

  public function delete()
  {
    if ($this->isDeletable()) {
      return parent::delete();
    }

    throw new NotDeletableModel('Data sudah diapprove tidak dapat dihapus');
  }
}
