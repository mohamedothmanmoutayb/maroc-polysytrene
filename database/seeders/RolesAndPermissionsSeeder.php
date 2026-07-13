<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear Spatie cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Save current admin user IDs before wiping, so we can reassign them to Super Admin
        $adminUserIds = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->whereIn('roles.name', ['admin', 'Super Admin'])
            ->pluck('model_has_roles.model_id')
            ->unique()
            ->toArray();

        // Wipe all existing role/permission data
        DB::table('role_has_permissions')->delete();
        DB::table('model_has_roles')->delete();
        DB::table('model_has_permissions')->delete();
        Role::query()->delete();
        Permission::query()->delete();

        // ─────────────────────────────────────────
        // PERMISSIONS
        // ─────────────────────────────────────────
        $definitions = [
            // Dashboard
            ['name' => 'view_dashboard',                    'module' => 'Dashboard',            'description' => 'Accéder au tableau de bord'],

            // Products
            ['name' => 'view_products',                     'module' => 'Produits',              'description' => 'Voir la liste des produits'],
            ['name' => 'create_products',                   'module' => 'Produits',              'description' => 'Créer un produit'],
            ['name' => 'edit_products',                     'module' => 'Produits',              'description' => 'Modifier un produit'],
            ['name' => 'delete_products',                   'module' => 'Produits',              'description' => 'Supprimer un produit'],
            ['name' => 'export_products',                   'module' => 'Produits',              'description' => 'Exporter les produits'],
            ['name' => 'manage_product_stock',              'module' => 'Produits',              'description' => 'Gérer le stock produits'],

            // Product Families
            ['name' => 'view_families',                     'module' => 'Familles',              'description' => 'Voir les familles de produits'],
            ['name' => 'create_families',                   'module' => 'Familles',              'description' => 'Créer une famille'],
            ['name' => 'edit_families',                     'module' => 'Familles',              'description' => 'Modifier une famille'],
            ['name' => 'delete_families',                   'module' => 'Familles',              'description' => 'Supprimer une famille'],

            // Raw Materials
            ['name' => 'view_raw_materials',                'module' => 'Matières Premières',   'description' => 'Voir les matières premières'],
            ['name' => 'create_raw_materials',              'module' => 'Matières Premières',   'description' => 'Ajouter une matière première'],
            ['name' => 'edit_raw_materials',                'module' => 'Matières Premières',   'description' => 'Modifier une matière première'],
            ['name' => 'delete_raw_materials',              'module' => 'Matières Premières',   'description' => 'Supprimer une matière première'],
            ['name' => 'manage_raw_material_stock',         'module' => 'Matières Premières',   'description' => 'Gérer le stock MP'],
            ['name' => 'view_raw_material_purchases',       'module' => 'Matières Premières',   'description' => 'Voir les achats MP'],
            ['name' => 'create_raw_material_purchases',     'module' => 'Matières Premières',   'description' => 'Enregistrer un achat MP'],

            // Production Orders
            ['name' => 'view_production_orders',            'module' => 'Production',           'description' => 'Voir les ordres de production'],
            ['name' => 'create_production_orders',          'module' => 'Production',           'description' => 'Créer un ordre de production'],
            ['name' => 'edit_production_orders',            'module' => 'Production',           'description' => 'Modifier un ordre de production'],
            ['name' => 'delete_production_orders',          'module' => 'Production',           'description' => 'Supprimer un ordre de production'],
            ['name' => 'approve_production_orders',         'module' => 'Production',           'description' => 'Approuver un ordre de production'],
            ['name' => 'start_production_orders',           'module' => 'Production',           'description' => 'Démarrer la production'],
            ['name' => 'complete_production_orders',        'module' => 'Production',           'description' => 'Terminer la production'],
            ['name' => 'view_production_output',            'module' => 'Production',           'description' => 'Voir les sorties de production'],
            ['name' => 'create_production_output',          'module' => 'Production',           'description' => 'Saisir une sortie de production'],
            ['name' => 'view_production_consumption',       'module' => 'Production',           'description' => 'Voir la consommation MP'],
            ['name' => 'declare_production_waste',          'module' => 'Production',           'description' => 'Déclarer les chutes / déchets'],
            // NEW
            ['name' => 'print_production_orders',           'module' => 'Production',           'description' => 'Imprimer les ordres de production'],

            // Sales Orders
            ['name' => 'view_sales_orders',                 'module' => 'Ventes',               'description' => 'Voir les ventes'],
            ['name' => 'create_sales_orders',               'module' => 'Ventes',               'description' => 'Créer une vente'],
            ['name' => 'edit_sales_orders',                 'module' => 'Ventes',               'description' => 'Modifier une vente'],
            ['name' => 'delete_sales_orders',               'module' => 'Ventes',               'description' => 'Supprimer une vente'],
            ['name' => 'approve_sales_orders',              'module' => 'Ventes',               'description' => 'Approuver une vente'],

            // Quotations / Devis
            ['name' => 'view_sales_quotations',             'module' => 'Ventes',               'description' => 'Voir les devis'],
            ['name' => 'create_sales_quotations',           'module' => 'Ventes',               'description' => 'Créer un devis'],
            ['name' => 'edit_sales_quotations',             'module' => 'Ventes',               'description' => 'Modifier un devis'],
            ['name' => 'delete_sales_quotations',           'module' => 'Ventes',               'description' => 'Supprimer un devis'],

            // Invoices / Factures
            ['name' => 'view_sales_invoices',               'module' => 'Ventes',               'description' => 'Voir les factures'],
            ['name' => 'create_sales_invoices',             'module' => 'Ventes',               'description' => 'Créer une facture'],
            ['name' => 'edit_sales_invoices',               'module' => 'Ventes',               'description' => 'Modifier une facture'],
            ['name' => 'delete_sales_invoices',             'module' => 'Ventes',               'description' => 'Supprimer une facture'],

            // Credit Notes / Avoirs
            ['name' => 'view_credit_notes',                 'module' => 'Ventes',               'description' => 'Voir les avoirs'],
            ['name' => 'create_credit_notes',               'module' => 'Ventes',               'description' => 'Créer un avoir'],
            ['name' => 'edit_credit_notes',                 'module' => 'Ventes',               'description' => 'Modifier un avoir'],
            ['name' => 'delete_credit_notes',               'module' => 'Ventes',               'description' => 'Supprimer un avoir'],

            // Clients
            ['name' => 'view_clients',                      'module' => 'Clients',              'description' => 'Voir les clients'],
            ['name' => 'create_clients',                    'module' => 'Clients',              'description' => 'Ajouter un client'],
            ['name' => 'edit_clients',                      'module' => 'Clients',              'description' => 'Modifier un client'],
            ['name' => 'delete_clients',                    'module' => 'Clients',              'description' => 'Supprimer un client'],
            ['name' => 'view_client_situation',             'module' => 'Clients',              'description' => 'Voir la situation financière client'],
            // NEW
            ['name' => 'view_client_sales_history',         'module' => 'Clients',              'description' => "Voir l'historique des ventes d'un client"],

            // Suppliers
            ['name' => 'view_suppliers',                    'module' => 'Fournisseurs',         'description' => 'Voir les fournisseurs'],
            ['name' => 'create_suppliers',                  'module' => 'Fournisseurs',         'description' => 'Ajouter un fournisseur'],
            ['name' => 'edit_suppliers',                    'module' => 'Fournisseurs',         'description' => 'Modifier un fournisseur'],
            ['name' => 'delete_suppliers',                  'module' => 'Fournisseurs',         'description' => 'Supprimer un fournisseur'],
            ['name' => 'view_supplier_situation',           'module' => 'Fournisseurs',         'description' => 'Voir la situation fournisseur'],

            // Employees
            ['name' => 'view_employees',                    'module' => 'Employés',             'description' => 'Voir les employés'],
            ['name' => 'create_employees',                  'module' => 'Employés',             'description' => 'Ajouter un employé'],
            ['name' => 'edit_employees',                    'module' => 'Employés',             'description' => 'Modifier un employé'],
            ['name' => 'delete_employees',                  'module' => 'Employés',             'description' => 'Supprimer un employé'],
            ['name' => 'manage_employee_documents',         'module' => 'Employés',             'description' => 'Gérer les documents employés'],

            // Attendance
            ['name' => 'view_attendance',                   'module' => 'Présences',            'description' => 'Voir les présences'],
            ['name' => 'manage_attendance',                 'module' => 'Présences',            'description' => 'Gérer les présences'],
            ['name' => 'view_attendance_reports',           'module' => 'Présences',            'description' => 'Voir les rapports de présence'],

            // Expenses
            ['name' => 'view_expenses',                     'module' => 'Dépenses',             'description' => 'Voir les dépenses'],
            ['name' => 'create_expenses',                   'module' => 'Dépenses',             'description' => 'Ajouter une dépense'],
            ['name' => 'edit_expenses',                     'module' => 'Dépenses',             'description' => 'Modifier une dépense'],
            ['name' => 'delete_expenses',                   'module' => 'Dépenses',             'description' => 'Supprimer une dépense'],
            ['name' => 'approve_expenses',                  'module' => 'Dépenses',             'description' => 'Approuver une dépense'],

            // Checks & Traites
            ['name' => 'view_checks',                       'module' => 'Paiements',            'description' => 'Voir les chèques'],
            ['name' => 'create_checks',                     'module' => 'Paiements',            'description' => 'Ajouter un chèque'],
            ['name' => 'manage_checks',                     'module' => 'Paiements',            'description' => 'Gérer les chèques'],
            ['name' => 'view_traites',                      'module' => 'Paiements',            'description' => 'Voir les traites'],
            ['name' => 'create_traites',                    'module' => 'Paiements',            'description' => 'Ajouter une traite'],
            ['name' => 'manage_traites',                    'module' => 'Paiements',            'description' => 'Gérer les traites'],

            // Recharge Parts
            ['name' => 'view_recharge_parts',               'module' => 'Pièces de Rechange',  'description' => 'Voir les pièces de rechange'],
            ['name' => 'create_recharge_parts',             'module' => 'Pièces de Rechange',  'description' => 'Ajouter une pièce'],
            ['name' => 'edit_recharge_parts',               'module' => 'Pièces de Rechange',  'description' => 'Modifier une pièce'],
            ['name' => 'delete_recharge_parts',             'module' => 'Pièces de Rechange',  'description' => 'Supprimer une pièce'],
            ['name' => 'adjust_recharge_parts_stock',       'module' => 'Pièces de Rechange',  'description' => 'Ajuster le stock pièces'],

            // Inventory
            ['name' => 'view_inventory',                    'module' => 'Inventaire',           'description' => 'Voir l\'inventaire avec stocks actuels'],
            ['name' => 'adjust_inventory',                  'module' => 'Inventaire',           'description' => 'Saisir un ajustement d\'inventaire (sans voir le stock)'],
            ['name' => 'approve_inventory_adjustments',     'module' => 'Inventaire',           'description' => 'Approuver les ajustements d\'inventaire'],
            ['name' => 'export_inventory',                  'module' => 'Inventaire',           'description' => 'Exporter l\'inventaire'],

            // Purchases / Règlements
            ['name' => 'view_purchases',                    'module' => 'Règlements',           'description' => 'Voir les règlements'],
            ['name' => 'create_purchases',                  'module' => 'Règlements',           'description' => 'Ajouter un règlement'],
            ['name' => 'edit_purchases',                    'module' => 'Règlements',           'description' => 'Modifier un règlement'],
            ['name' => 'delete_purchases',                  'module' => 'Règlements',           'description' => 'Supprimer un règlement'],

            // Fleet – Véhicules
            ['name' => 'view_vehicles',                     'module' => 'Parc Auto',            'description' => 'Voir les véhicules'],
            ['name' => 'create_vehicles',                   'module' => 'Parc Auto',            'description' => 'Ajouter un véhicule'],
            ['name' => 'edit_vehicles',                     'module' => 'Parc Auto',            'description' => 'Modifier un véhicule'],
            ['name' => 'delete_vehicles',                   'module' => 'Parc Auto',            'description' => 'Supprimer un véhicule'],
            ['name' => 'manage_vehicle_documents',          'module' => 'Parc Auto',            'description' => 'Gérer les documents véhicules'],

            // Fleet – Machines
            ['name' => 'view_machines',                     'module' => 'Parc Auto',            'description' => 'Voir les machines'],
            ['name' => 'create_machines',                   'module' => 'Parc Auto',            'description' => 'Ajouter une machine'],
            ['name' => 'edit_machines',                     'module' => 'Parc Auto',            'description' => 'Modifier une machine'],
            ['name' => 'delete_machines',                   'module' => 'Parc Auto',            'description' => 'Supprimer une machine'],

            // Machine preventive maintenance
            ['name' => 'view_machine_maintenance',          'module' => 'Parc Auto',            'description' => 'Voir la maintenance préventive'],
            ['name' => 'create_machine_maintenance',        'module' => 'Parc Auto',            'description' => 'Créer un programme de maintenance'],
            ['name' => 'edit_machine_maintenance',          'module' => 'Parc Auto',            'description' => 'Modifier un programme de maintenance'],
            ['name' => 'delete_machine_maintenance',        'module' => 'Parc Auto',            'description' => 'Supprimer un programme de maintenance'],
            ['name' => 'complete_machine_maintenance',      'module' => 'Parc Auto',            'description' => 'Confirmer une maintenance effectuée'],

            // Fleet – Drivers
            ['name' => 'view_drivers',                      'module' => 'Parc Auto',            'description' => 'Voir les chauffeurs'],
            ['name' => 'create_drivers',                    'module' => 'Parc Auto',            'description' => 'Ajouter un chauffeur'],
            ['name' => 'edit_drivers',                      'module' => 'Parc Auto',            'description' => 'Modifier un chauffeur'],
            ['name' => 'delete_drivers',                    'module' => 'Parc Auto',            'description' => 'Supprimer un chauffeur'],

            // Administration
            ['name' => 'manage_roles',                      'module' => 'Administration',       'description' => 'Gérer les rôles'],
            ['name' => 'manage_permissions',                'module' => 'Administration',       'description' => 'Gérer les permissions'],
            ['name' => 'manage_users',                      'module' => 'Administration',       'description' => 'Gérer les utilisateurs'],
            ['name' => 'view_system_logs',                  'module' => 'Administration',       'description' => 'Voir les journaux système'],
            ['name' => 'manage_settings',                   'module' => 'Administration',       'description' => 'Gérer les paramètres'],
        ];

        foreach ($definitions as $def) {
            Permission::create([
                'name'        => $def['name'],
                'guard_name'  => 'web',
                'module'      => $def['module'],
                'description' => $def['description'],
            ]);
        }

        // ─────────────────────────────────────────
        // ROLE 1 – Production  (sans option supprimer)
        // ─────────────────────────────────────────
        $production = Role::create(['name' => 'Production', 'guard_name' => 'web']);
        $production->syncPermissions([
            'view_dashboard',

            // Matières Premières – sans achat, sans delete
            'view_raw_materials',
            'create_raw_materials',
            'edit_raw_materials',
            'manage_raw_material_stock',

            // Ordres de production – sans delete, peut approuver + démarrer + créer
            'view_production_orders',
            'create_production_orders',
            'edit_production_orders',
            'approve_production_orders',
            'start_production_orders',
            'complete_production_orders',
            'view_production_output',
            'create_production_output',
            'view_production_consumption',
            'declare_production_waste',
            'print_production_orders',

            // Machines – All
            'view_machines',
            'create_machines',
            'edit_machines',
            'delete_machines',

            // Maintenance préventive – All
            'view_machine_maintenance',
            'create_machine_maintenance',
            'edit_machine_maintenance',
            'delete_machine_maintenance',
            'complete_machine_maintenance',
        ]);

        // ─────────────────────────────────────────
        // ROLE 2 – Decoupage  (sans option supprimer)
        // ─────────────────────────────────────────
        $decoupage = Role::create(['name' => 'Decoupage', 'guard_name' => 'web']);
        $decoupage->syncPermissions([
            'view_dashboard',

            // Ordres de production – peut créer + démarrer uniquement (pas approuver, pas delete)
            'view_production_orders',
            'create_production_orders',
            'start_production_orders',

            // Déclaration de déchet
            'declare_production_waste',

            // Inventaire – saisie + approbation SANS voir le stock actuel
            // (view_inventory is intentionally excluded)
            'adjust_inventory',
            'approve_inventory_adjustments',
        ]);

        // ─────────────────────────────────────────
        // ROLE 3 – Vente et Caisse  (avec option supprimer)
        // ─────────────────────────────────────────
        $venteCaisse = Role::create(['name' => 'Vente et Caisse', 'guard_name' => 'web']);
        $venteCaisse->syncPermissions([
            'view_dashboard',

            // Ventes (par jour – delete autorisé)
            'view_sales_orders',
            'create_sales_orders',
            'edit_sales_orders',
            'delete_sales_orders',

            // Clients – sans historique des ventes, sans delete
            'view_clients',
            'create_clients',
            'edit_clients',
            'view_client_situation',
            // view_client_sales_history intentionally excluded

            // Devis
            'view_sales_quotations',
            'create_sales_quotations',
            'edit_sales_quotations',
            'delete_sales_quotations',

            // Dépenses (par jour)
            'view_expenses',
            'create_expenses',
            'edit_expenses',
            'delete_expenses',

            // Traites et Chèques (par jour)
            'view_traites',
            'create_traites',
            'manage_traites',
            'view_checks',
            'create_checks',
            'manage_checks',

            // Véhicules
            'view_vehicles',
            'create_vehicles',
            'edit_vehicles',
            'delete_vehicles',
            'manage_vehicle_documents',
        ]);

        // ─────────────────────────────────────────
        // ROLE 4 – Manager  (tout sauf gestion factures + administration)
        // Peut voir et imprimer les factures, machines, ordres de production
        // ─────────────────────────────────────────
        $excludedFromManager = [
            // Invoice management (can VIEW but not create/edit/delete)
            'create_sales_invoices',
            'edit_sales_invoices',
            'delete_sales_invoices',
            // Administration panel
            'manage_roles',
            'manage_permissions',
            'manage_users',
            'view_system_logs',
            'manage_settings',
        ];

        $manager = Role::create(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->syncPermissions(
            Permission::whereNotIn('name', $excludedFromManager)->pluck('name')->toArray()
        );

        // ─────────────────────────────────────────
        // ROLE 5 – Super Admin  (tout)
        // Also kept as 'admin' alias so AdminMiddleware keeps working
        // ─────────────────────────────────────────
        $superAdmin = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Keep legacy 'admin' role so AdminMiddleware / isAdmin() still works
        $adminAlias = Role::create(['name' => 'admin', 'guard_name' => 'web']);
        $adminAlias->syncPermissions(Permission::all());

        // Restore former admins → Super Admin + admin alias
        foreach ($adminUserIds as $userId) {
            $user = User::find($userId);
            if ($user) {
                $user->syncRoles(['Super Admin', 'admin']);
            }
        }

        // Clear cache again after everything is set
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
