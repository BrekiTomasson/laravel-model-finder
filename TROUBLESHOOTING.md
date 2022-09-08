# `TROUBLESHOOTING.md`

## Do two (or more) of your models share the same class name? 

If you have two Models with the same name but different namespaces, say `App\Models\Auth\User` and `App\Models\Logs\User`, you may run into problems if you
want to make both of them searchable. This is because the generation of the cache key does not take the namespace into account, just the Model's class name.
However, having two Models of the same name is most likely such an edge case that nobody will ever run into this problem. A future version of this package
will fix this by basing the cache key on the database table name instead of the Model's name.

## Have you built two (or more) finder classes referencing the same model

If you were to build two Finder classes that are based on the same model but base the results on the contents of different columns, you will most likely run
into the same problem as described above, when two different Models share the same name. For this reason, try to keep it simple by only creating one Finder 
class per Model you want to be searchable.

### Are one or more of your database columns case-sensitive?

If you have a case-sensitive column defined in your database, where 'Billy' and 'billy' are considered to be different unique values, this package will most
likely run into issues, as all columns are searched in a case-insensitive manner. 

This is something that will likely be revisited in a future version of the package, allowing you to define case-sensitivity globally or per column.
