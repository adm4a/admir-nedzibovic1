<?php
require_once __DIR__ . "/../dao/FinalDao.php";

class FinalService
{
    protected $dao;

    public function __construct()
    {
        $this->dao = new FinalDao();
    }
    /** TODO
    * Implement service method to login user
    */
    public function login($email, $password)
    {
        return $this->dao->login($email, $password);
    }
    /** TODO
    * Implement service method to add new investor to investor table and cap-table
    */

    public function investor($first_name, $last_name, $email, $company, $share_class_id, $share_class_category_id, $diluted_shares)
    {
        return $this->dao->investor($first_name, $last_name, $email, $company, $share_class_id, $share_class_category_id, $diluted_shares);
    }

    /** TODO
    * Implement service method to return list of all share classes from share_classes table
    */

    public function share_classes()
    {
        return $this->dao->share_classes();
    }
    /** TODO
    * Implement service method to return list of all share class categories from share_class_categories table
    */
    public function share_class_categories()
    {
        return $this->dao->share_class_categories();
    }
}
?>



