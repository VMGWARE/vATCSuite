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
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
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
    }

    /**
     * Define what happens when the List operation is loaded.
     * 
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        CRUD::setFromDb(); // set columns from db columns.

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ATISAudioFileRequest::class);
        CRUD::setFromDb(); // set fields from db columns.

        /**
         * Fields can be defined using the fluent syntax:
         * - CRUD::field('price')->type('number');
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     * 
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
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
        Storage::delete('public/atis/' . $id . '/' . $name);

        // Check if the file was deleted
        if (Storage::exists('public/atis/' . $id . '/' . $name)) {
            abort(500);
        }

        // Delete the database entry
        return CRUD::delete($id);
    }
}
