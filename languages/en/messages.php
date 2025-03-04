<?php
return [
    // General
    'welcome' => 'Welcome',
    'login' => 'Login',
    'logout' => 'Logout',
    'profile' => 'Profile',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'delete' => 'Delete',
    'edit' => 'Edit',
    'add' => 'Add',
    
    // Menu
    'dashboard' => 'Dashboard',
    'physical_servers' => 'Physical Servers',
    'virtual_servers' => 'Virtual Servers',
    'services' => 'Services',
    'projects' => 'Projects',
    'locations' => 'Locations',
    'users' => 'Users',
    
    // Server Management
    'server_name' => 'Server Name',
    'ip_address' => 'IP Address',
    'ram' => 'RAM',
    'cpu' => 'CPU',
    'disk' => 'Disk',
    'location' => 'Location',
    'project' => 'Project',
    'status' => 'Status',
    'created_at' => 'Created At',
    
    // Services
    'service_name' => 'Service Name',
    'port' => 'Port',
    'description' => 'Description',
    
    // Projects
    'project_name' => 'Project Name',
    'project_code' => 'Project Code',
    
    // Users
    'username' => 'Username',
    'full_name' => 'Full Name',
    'email' => 'Email',
    'password' => 'Password',
    'role' => 'Role',
    
    // Messages
    'success' => 'Operation completed successfully.',
    'error' => 'An error occurred.',
    'confirm_delete' => 'Are you sure you want to delete?',
    
    // Statuses
    'active' => 'Active',
    'passive' => 'Passive',
    'completed' => 'Completed',
    
    // Admin Page
    'user_management' => 'User Management',
    'users_list' => 'Users',
    'add_new_user' => 'Add New User',
    'edit_user' => 'Edit User',
    'user_id' => 'ID',
    'last_login' => 'Last Login',
    'actions' => 'Actions',
    'new_password' => 'New Password',
    'password_help' => 'Leave empty if you don\'t want to change the password.',
    'username_help' => 'Username cannot be changed.',
    'user_role_admin' => 'Admin',
    'user_role_user' => 'User',
    
    // Error and Success Messages
    'error_username_email_exists' => 'This username or email is already in use!',
    'error_email_exists' => 'This email is already used by another user!',
    'success_user_added' => 'User added successfully.',
    'success_user_updated' => 'User updated successfully.',
    'error_no_access' => 'You don\'t have permission to access this page!',
    
    // Physical Server Edit
    'edit_physical_server' => 'Edit Physical Server',
    'back_to_physical_servers' => 'Back to Physical Servers',
    'server_details' => 'Server Details',
    'select_location' => 'Select Location',
    'add_new_location' => 'Add New Location',
    'select_project' => 'Select Project (Optional)',
    'add_new_project' => 'Add New Project',
    'cpu_cores' => 'CPU Cores',
    'memory' => 'Memory',
    'cpu_placeholder' => 'Ex: Intel Xeon E5-2680 v4 2.40GHz',
    'memory_placeholder' => 'Ex: 64GB DDR4',
    'disk_placeholder' => 'Ex: 2x 500GB SSD RAID1',
    'update' => 'Update',
    'error_updating_server' => 'An error occurred while updating the server',
    
    // Physical Server Add
    'add_physical_server' => 'Add New Physical Server',
    'error_adding_server' => 'An error occurred while adding the server',
    'total_cores' => 'Total CPU Cores',
    'memory_capacity' => 'Memory Capacity',
    'disk_capacity' => 'Total Disk Capacity',
    
    // Physical Server Delete
    'error_has_virtual_servers' => 'This physical server cannot be deleted because it has {sayi} virtual servers attached. You need to delete the attached virtual servers first.',
    'success_server_deleted' => 'Physical server has been successfully deleted.',
    
    // Service Management
    'add_service' => 'Add New Service',
    'edit_service' => 'Edit Service',
    'default_port' => 'Default Port',
    'existing_services' => 'Existing Services',
    'service_updated' => 'Service updated successfully.',
    'service_added' => 'New service added successfully.',
    'service_usage' => 'Usage',
    'not_in_use' => 'Not in use',
    'server_count' => '{count} servers',
    'confirm_delete_service' => 'Are you sure you want to delete this service?',
    'new_service' => 'New Service',
    
    // Service Delete
    'error_service_id_missing' => 'Service ID not specified.',
    'error_service_in_use' => 'This service is being used by {count} virtual servers. Please remove the service from these servers first.',
    'success_service_deleted' => 'Service has been successfully deleted.',
    'error_deleting_service' => 'An error occurred while deleting the service: {error}',
    
    // Home Page
    'server_tracking_system' => 'Server Tracking System',
    'hardware' => 'Hardware',
    'virtual_servers_count' => '{count} virtual servers',
    'no_virtual_servers' => 'None',
    'virtual_servers_button' => 'Virtual Servers',
    'physical_server_resources' => 'Physical Server Resources:',
    'core_usage' => 'Core Usage',
    'memory_usage' => 'Memory Usage',
    'disk_usage' => 'Disk Usage',
    'cores' => 'Cores',
    'core_count' => '{used}/{total} Cores',
    'gb_count' => '{used}/{total} GB',
    'hardware_cpu' => 'Cores: {value}',
    'hardware_memory' => 'Memory: {value}',
    'hardware_disk' => 'Disk: {value}',
    'no_physical_servers' => 'No physical servers have been added yet.',
    
    // Login Page
    'login_page_title' => 'Login - Server Tracking System',
    'error_invalid_credentials' => 'Invalid username or password!',
    'login_form_title' => 'Server Tracking System',
    'username_placeholder' => 'Username',
    'password_placeholder' => 'Password',
    'login_button' => 'Login',
    
    // Location Management
    'location_management' => 'Location Management',
    'add_new_location_title' => 'Add New Location',
    'edit_location_title' => 'Edit Location',
    'location_name' => 'Location Name',
    'existing_locations' => 'Existing Locations',
    'server_count_info' => '{count} servers',
    'no_servers' => 'No servers',
    'error_location_exists' => 'Error: This location name is already in use!',
    'success_location_added' => 'New location added successfully.',
    'success_location_updated' => 'Location updated successfully.',
    'confirm_delete_location' => 'Are you sure you want to delete this location?',
    
    // Location Delete
    'error_location_has_servers' => 'This location cannot be deleted because it has attached servers.',
    'success_location_deleted' => 'Location has been successfully deleted.',
    
    // Profile Page
    'profile_page_title' => 'Profile - {name}',
    'profile_information' => 'Profile Information',
    'account_information' => 'Account Information',
    'current_password' => 'Current Password',
    'new_password' => 'New Password',
    'current_password_help' => 'Enter your current password to confirm changes.',
    'registration_date' => 'Registration Date',
    'error_current_password' => 'Current password is incorrect!',
    'success_profile_updated' => 'Your profile has been updated successfully.',
    'error_updating_profile' => 'Error: {error}',
    
    // Project Management
    'project_management' => 'Project Management',
    'add_new_project' => 'Add New Project',
    'edit_project' => 'Edit Project',
    'project_code' => 'Project Code',
    'project_code_placeholder' => 'Ex: PRJ-2024',
    'existing_projects' => 'Existing Projects',
    'error_project_code_exists' => 'Error: This project code is already in use!',
    'success_project_added' => 'New project added successfully.',
    'success_project_updated' => 'Project updated successfully.',
    'error_updating_project' => 'Error: {error}',
    'physical_server_count' => '{count} physical',
    'virtual_server_count' => '{count} virtual',
    'confirm_delete_project' => 'Are you sure you want to delete this project?',
    
    // Project Delete
    'error_project_has_servers' => 'This project cannot be deleted because it has {fiziksel} physical servers and {sanal} virtual servers.',
    'error_project_has_physical_servers' => 'This project cannot be deleted because it has {count} physical servers.',
    'error_project_has_virtual_servers' => 'This project cannot be deleted because it has {count} virtual servers.',
    'success_project_deleted' => 'Project has been successfully deleted.',
    'error_deleting_project' => 'An error occurred while deleting the project: {error}',
    
    // Virtual Server Detail
    'virtual_server_detail' => 'Virtual Server Detail',
    'back_to_virtual_servers' => 'Back to Virtual Servers',
    'running_services' => 'Running Services',
    'add_existing_service' => 'Add Existing Service',
    'add_new_service' => 'Add New Service',
    'service_port' => 'Port',
    'service_notes' => 'Notes',
    'default_port' => 'Default port',
    'no_services_added' => 'No services have been added yet.',
    'edit_service' => 'Edit Service',
    'remove_service' => 'Remove',
    'confirm_remove_service' => 'Are you sure you want to remove this service?',
    'service_added_success' => 'New service has been successfully added and assigned to the server.',
    'service_add_error' => 'Error while adding service: {error}',
    'service_assign_error' => 'Service added but error while assigning to server: {error}',
    'service_updated_success' => 'Service has been successfully updated.',
    'service_removed_success' => 'Service has been successfully removed.',
    'service_action_error' => 'Error: {error}',
    'physical_server' => 'Physical Server',
    'project_info' => '{project_name} ({project_code})',
    'no_project_assigned' => '-',
    'cancel' => 'Cancel',
    'update' => 'Update',
    
    // Virtual Server Detail - Additional Texts
    'select_service' => 'Select Service',
    'service_not_in_list' => 'Can\'t find the service you\'re looking for?',
    'custom_port_optional' => 'Custom Port (Optional)',
    'default_port_info' => 'Default port will be used if left empty.',
    'add_service' => 'Add Service',
    'no_active_services' => 'No active services available to add.',
    
    // Virtual Server Edit
    'edit_virtual_server' => 'Edit Virtual Server',
    'back_to_virtual_servers' => 'Back to Virtual Servers',
    'error_virtual_server_id' => 'Virtual server ID not specified.',
    'error_virtual_server_not_found' => 'Virtual server not found.',
    'error_ip_in_use' => 'This IP address is already in use by another server.',
    'success_virtual_server_updated' => 'Virtual server has been successfully updated.',
    'error_updating_virtual_server' => 'Error: {error}',
    'available_resources' => 'Available: {cpu} Cores, {ram} GB RAM, {disk} GB Disk',
    'only_servers_with_resources' => 'Only physical servers with sufficient resources are listed.',
    'cpu_placeholder' => 'Ex: 4 Cores',
    'ram_placeholder' => 'Ex: 8GB',
    'disk_placeholder' => 'Ex: 100GB',
    'save_changes' => 'Save Changes',
    'resource_validation_error' => 'Please fix the following errors:',
    'error_cpu_capacity' => 'CPU value cannot exceed the remaining capacity of the selected physical server ({value} Cores).',
    'error_ram_capacity' => 'RAM value cannot exceed the remaining capacity of the selected physical server ({value} GB).',
    'error_disk_capacity' => 'Disk value cannot exceed the remaining capacity of the selected physical server ({value} GB).',
    'enter_valid_ipv4' => 'Please enter a valid IPv4 address',
    'enter_valid_number' => 'Please enter only numbers (ex: {example})',
    
    // Virtual Server Add
    'add_virtual_server' => 'Add New Virtual Server',
    'error_physical_server_id' => 'Physical server ID not specified.',
    'error_physical_server_not_found' => 'Physical server not found.',
    'resource_usage' => 'Resource Usage:',
    'remaining_resources' => 'Available: {value}',
    'success_virtual_server_added' => 'Virtual server has been successfully added.',
    'error_adding_virtual_server' => 'Error: {error}',
    'default_project_info' => 'The physical server\'s project is selected by default. You can choose a different project if needed.',
    'error_cpu_limit' => 'CPU value cannot exceed the remaining capacity ({value} Cores).',
    'error_ram_limit' => 'RAM value cannot exceed the remaining capacity ({value} GB).',
    'error_disk_limit' => 'Disk value cannot exceed the remaining capacity ({value} GB).',
    'add_virtual_server_button' => 'Add Virtual Server',
]; 