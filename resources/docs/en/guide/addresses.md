# Address Management

**Revolutionize your address handling** with Laravel Nusa's intelligent address management system. From e-commerce checkout flows to enterprise location management, our solution transforms complex Indonesian address requirements into seamless user experiences.

## Why Laravel Nusa Address Management?

### 🎯 **Business-Critical Benefits**

**Simplified Address Forms**: Streamlined input with cascading dropdowns
**Improved Delivery Accuracy**: Address validation against official data
**Consistent Data Structure**: Handle Indonesia's administrative hierarchy properly
**Better User Experience**: Intuitive address selection process

### 🚀 **Enterprise-Ready Features**

- **Multi-Address Support** - Customers can manage multiple shipping addresses
- **Address Validation** - Real-time verification against official data
- **Smart Auto-Complete** - Cascading dropdowns with intelligent suggestions
- **Flexible Integration** - Works with any existing user model

## Real Business Solutions

### 🛒 **E-Commerce Applications**

**Challenge**: Complex address forms can confuse customers during checkout, especially with Indonesia's multi-level administrative structure.

**Solution**: Streamline the checkout process with intelligent address management:

```php
// Smart address management for customers
class Customer extends Model
{
    use WithAddresses;

    public function getDefaultShippingAddress()
    {
        return $this->addresses()
            ->where('type', 'shipping')
            ->where('is_default', true)
            ->first();
    }

    public function calculateShippingCost($productWeight)
    {
        $address = $this->getDefaultShippingAddress();
        $zone = $address->getShippingZone();

        return $zone->calculateCost($productWeight);
    }
}
```

**Benefits**:
- Reduced form complexity and user confusion
- Improved address data quality and delivery success
- Consistent address formatting across the application
- Better customer experience during checkout

### 🏢 **Multi-Location Business Management**

**Challenge**: Organizations with multiple locations need to manage addresses consistently across different offices, warehouses, and service centers.

**Solution**: Centralized address management for complex organizational structures:

```php
// Multi-location business management
class Company extends Model
{
    use WithAddresses;

    public function getLocationsByType($type)
    {
        return $this->addresses()
            ->where('type', $type)
            ->with(['village.regency.province'])
            ->get();
    }

    public function getRegionalCoverage()
    {
        return $this->addresses()
            ->with('province')
            ->get()
            ->groupBy('province.name')
            ->map(function ($addresses, $province) {
                return [
                    'province' => $province,
                    'locations' => $addresses->count(),
                    'types' => $addresses->pluck('type')->unique()
                ];
            });
    }
}
```

**Benefits**:
- Centralized management of all business locations
- Regional coverage analysis and reporting
- Consistent address data across the organization
- Simplified compliance and administrative reporting

## Quick Setup (2 Minutes)

### 1. Install Address Tables

```bash
php artisan vendor:publish --tag=creasi-migrations
php artisan migrate
```

### 2. Add to Your Models

```php
use Creasi\Nusa\Models\Concerns\WithAddress;

class User extends Model
{
    use WithAddress; // Single address support
}
```

### 3. Start Using

```php
$user->address()->create([
    'address_line' => 'Jl. Sudirman No. 123',
    'village_code' => '33.74.01.1001',
    'postal_code' => '50132'
]);
```

## Advanced Business Features

### 🎯 **Smart Address Validation**

Ensure 100% delivery accuracy with intelligent validation:

```php
// Automatic address validation
class AddressValidator
{
    public function validateAddress(array $addressData)
    {
        $village = Village::find($addressData['village_code']);

        if (!$village) {
            throw new InvalidAddressException('Invalid village code');
        }

        // Auto-correct parent codes
        $addressData['district_code'] = $village->district_code;
        $addressData['regency_code'] = $village->regency_code;
        $addressData['province_code'] = $village->province_code;

        // Validate postal code
        if (!$addressData['postal_code']) {
            $addressData['postal_code'] = $village->postal_code;
        }

        return $addressData;
    }
}
```

**Benefits**:
- Improved address data quality and consistency
- Automatic postal code completion when missing
- Standardized address formatting
- Reduced data entry errors

### 📍 **Multi-Address Management**

Perfect for customers with multiple delivery locations:

```php
// Customer with multiple addresses
class Customer extends Model
{
    use WithAddresses;

    public function addShippingAddress(array $addressData)
    {
        return $this->addresses()->create(array_merge($addressData, [
            'type' => 'shipping'
        ]));
    }

    public function setDefaultShippingAddress($addressId)
    {
        // Remove default from all shipping addresses
        $this->addresses()
            ->where('type', 'shipping')
            ->update(['is_default' => false]);

        // Set new default
        return $this->addresses()
            ->where('id', $addressId)
            ->update(['is_default' => true]);
    }

    public function getShippingOptions()
    {
        return $this->addresses()
            ->where('type', 'shipping')
            ->with(['village.regency.province'])
            ->get()
            ->map(function ($address) {
                return [
                    'id' => $address->id,
                    'label' => $address->name . ' - ' . $address->village->name,
                    'full_address' => $address->full_address,
                    'shipping_cost' => $address->calculateShippingCost(),
                    'is_default' => $address->is_default
                ];
            });
    }
}
```

**Benefits**:
- Enhanced customer experience with saved addresses
- Streamlined checkout process for returning customers
- Flexible address management for different use cases

## Common Use Cases

### 📊 **Typical Applications**

**E-Commerce Platforms**
- Streamlined checkout flows with cascading address dropdowns
- Improved delivery accuracy through address validation
- Better customer experience with saved shipping addresses
- Consistent address formatting across the platform

**Multi-Location Businesses**
- Centralized management of office and warehouse locations
- Standardized address data across different business units
- Regional analysis and reporting capabilities
- Simplified compliance with administrative requirements

**Service-Based Applications**
- Customer address management for service delivery
- Territory and coverage area management
- Location-based service optimization
- Geographic analysis and reporting

## Implementation Patterns

### 🎯 **Single Address Pattern**
Perfect for stores, offices, or simple user profiles:

```php
class Store extends Model
{
    use WithAddress;
    // One location per store
}
```

### 🏢 **Multi-Address Pattern**
Ideal for customers, companies, or service providers:

```php
class Customer extends Model
{
    use WithAddresses;
    // Multiple shipping/billing addresses
}
```

### 🚀 **Enterprise Pattern**
For complex business requirements:

```php
class Enterprise extends Model
{
    use WithAddresses, WithCoordinate;
    // Multiple locations + GPS coordinates
    // Perfect for logistics and analytics
}
```

## Getting Started

Laravel Nusa's address management system provides a solid foundation for handling Indonesian addresses in your applications.

### **Next Steps**:

1. **[Installation Guide](/en/guide/installation)** - Set up Laravel Nusa in your project
2. **[Customization Options](/en/guide/customization)** - Learn about available traits and features
3. **[Address Forms Examples](/en/examples/address-forms)** - See practical implementation examples
4. **[API Reference](/en/api/models/address)** - Explore detailed technical documentation

### **Key Features**:

- **[Address Validation](/en/api/concerns/with-address)** - Ensure data consistency and accuracy
- **[Multi-Address Support](/en/api/concerns/with-addresses)** - Handle complex address requirements
- **[Geographic Features](/en/api/concerns/with-coordinate)** - Add location-based functionality

---

*Simplify Indonesian address management in your Laravel applications.*
