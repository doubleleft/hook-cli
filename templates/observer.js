/**
 * Custom observer for: {collection}
 */

class {name}
{

    // customize response here
    toArray(model, array)
    {
        return array;
    }

    // before create
    creating(model)
    {
    }

    // after create
    created(model)
    {
    }

    // before update
    updating(model)
    {
    }

    // after update
    updated(model)
    {
    }

    // before save
    saving(model)
    {
    }

    // after save
    saved(model)
    {
    }

    // before delete
    deleting(model)
    {
    }

    // after delete
    deleted(model)
    {
    }

    // before update multiple rows
    updating_multiple(query, values)
    {
        return values;
    }

    // before delete multiple rows
    deleting_multiple(query)
    {
    }

}

