<?php 

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Model::unguard();
         
        $this->call(RolesTableSeeder::class);
        $this->call(AgentRoleSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(QuizTablesSeeder::class);
        $this->call(AffiliateRoleTableSeeder::class);
        $this->call(CountryStateCitySeeder::class);
        $this->call(PersonAuxTablesSeeder::class);
        $this->call(PlanDeducibleType::class);
        $this->call(ProcedureNewPolicySeeder::class);
        $this->call(ProcedureClaimsSeeder::class);
        $this->call(ClaimSettlementSeeder::class);
        $this->call(ProcedureDocumentSeeder::class);
        $this->call(CsRoleSeeder::class);
        //Insurance Company Seeder
        $this->call("\Modules\InsuranceCompany\Database\Seeders\DBCompanyDatabaseSeeder");

        Model::reguard();
    }
}
