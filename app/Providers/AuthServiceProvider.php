<?php

namespace App\Providers;

use App\Policies\{
    CompanyPolicy,
    BranchPolicy,
    EmployeePolicy,
    ProfilePolicy,
    ProductPolicy,
    SupplierPolicy,
    UserPolicy,
    WarehousePolicy,
};
use App\Models\{
    Company,
    Branch,
    Employee,
    Profile,
    Product,
    Supplier,
    User,
    Warehouse,
};
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Company::class => CompanyPolicy::class,
        Branch::class => BranchPolicy::class,
        Employee::class => EmployeePolicy::class,
        Product::class => ProductPolicy::class,
        Profle::class => ProfilePolicy::class,
        Supplier::class => SupplierPolicy::class,
        User::class => UserPolicy::class,
        Warehouse::class => WarehousePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('company-read', [CompanyPolicy::class, 'viewAny']);
        Gate::define('company-create', [CompanyPolicy::class, 'create']);
        Gate::define('company-update', [CompanyPolicy::class, 'update']);
        Gate::define('company-delete', [CompanyPolicy::class, 'delete']);

        Gate::define('branch-read', [BranchPolicy::class, 'viewAny']);
        Gate::define('branch-create', [BranchPolicy::class, 'create']);
        Gate::define('branch-update', [BranchPolicy::class, 'update']);
        Gate::define('branch-delete', [BranchPolicy::class, 'delete']);

        Gate::define('warehouse-read', [WarehousePolicy::class, 'viewAny']);
        Gate::define('warehouse-create', [WarehousePolicy::class, 'create']);
        Gate::define('warehouse-update', [WarehousePolicy::class, 'update']);
        Gate::define('warehouse-delete', [WarehousePolicy::class, 'delete']);

        Gate::define('employee-read', [EmployeePolicy::class, 'viewAny']);
        Gate::define('employee-create', [EmployeePolicy::class, 'create']);
        Gate::define('employee-update', [EmployeePolicy::class, 'update']);
        Gate::define('employee-delete', [EmployeePolicy::class, 'delete']);

        Gate::define('product-read', [ProductPolicy::class, 'viewAny']);
        Gate::define('product-create', [ProductPolicy::class, 'create']);
        Gate::define('product-update', [ProductPolicy::class, 'update']);
        Gate::define('product-delete', [ProductPolicy::class, 'delete']);

        Gate::define('supplier-read', [SupplierPolicy::class, 'viewAny']);
        Gate::define('supplier-create', [SupplierPolicy::class, 'create']);
        Gate::define('supplier-update', [SupplierPolicy::class, 'update']);
        Gate::define('supplier-delete', [SupplierPolicy::class, 'delete']);

        Gate::define('profile-read', [ProfilePolicy::class, 'viewAny']);
        Gate::define('profile-update', [ProfilePolicy::class, 'update']);

        Gate::define('user-read', [UserPolicy::class, 'viewAny']);
        Gate::define('user-create', [UserPolicy::class, 'create']);
        Gate::define('user-update', [UserPolicy::class, 'update']);
    }
}
