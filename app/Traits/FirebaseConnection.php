<?php  
namespace App\Traits;
use Firebase\FirebaseLib;

trait FirebaseConnection {
  protected function ConnectionFirebase ()
  {
    return new FirebaseLib(env('FIREBASE_URL', 'null'), env('FIREBASE_TOKEN', 'null'));
  }
}