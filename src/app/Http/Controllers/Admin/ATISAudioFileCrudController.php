<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ATISAudioFileRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Storage;

/**
 * Class ATISAudioFileCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ATISAudioFileCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation {
        destroy as traitDestroy;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     * 
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\ATISAudioFile::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/atis-audio-file');
        CRUD::setEntityNameStrings('ATIS Audio File', 'ATIS Audio Files');
        $this->crud->denyAccess(['create', 'update']);
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // CRUD::setFromDb(); // set columns from db columns.

        // Define the columns you want to display in the list view
        CRUD::addColumn([
            'name' => 'icao',
            'label' => 'ICAO Code',
        ]);

        CRUD::addColumn([
            'name' => 'ident',
            'label' => 'Ident',
        ]);

        CRUD::addColumn([
            'name' => 'zulu',
            'label' => 'Zulu',
        ]);

        CRUD::addColumn([
            'name' => 'url',
            'label' => 'Audio URL',
            'type' => 'url', // This will make the column clickable
            'limit' => 100, // Truncate the URL text to 100 characters
        ]);

        CRUD::addColumn([
            'name' => 'expires_at',
            'label' => 'Expires At',
            'type' => 'datetime',
        ]);

        // Add more columns as needed
    }

    /**
     * Override the default destroy() method so that we can delete the file from the filesystem.
     * 
     * @param int $id
     * @return void
     */
    public function destroy($id)
    {
        // Check if the user has permission to delete files
        CRUD::hasAccessOrFail('delete');

        // Check if the file exists
        $atisFile = \App\Models\ATISAudioFile::find($id);
        if ($atisFile === null) {
            abort(404);
        }

        // Delete the file from the filesystem
        $id = $atisFile->id;
        $name = $atisFile->file_name;
        Storage::delete('atis/' . $id . '/' . $name);

        // Check if the file was deleted
        if (Storage::exists('atis/' . $id . '/' . $name)) {
            abort(500);
        }

        // Delete the database entry
        return CRUD::delete($id);
    }
}
