<?php

use Redooor\Redminportal\Media;

class MediaModelTest extends \RedminTestCase {

    public function testAll()
    {
        $medias = Media::all();
        $this->assertTrue($medias != null);
    }

    public function testFind1Fails()
    {
        $media = Media::find(1);
        $this->assertTrue($media == null);
    }

    public function testCreateNew()
    {
        $media = new Media;
        $media->name = 'This is the title';
        $media->path = 'path/to/somewhere';
        $media->sku = 'UNIQUESKU001';
        $media->short_description = 'This is the body';
        $media->category_id = 1;
        $media->active = true;

        $result = $media->save();

        $this->assertTrue($media->id == 1);
        $this->assertTrue($media->name == 'This is the title');
        $this->assertTrue($media->path == 'path/to/somewhere');
        $this->assertTrue($media->sku == 'UNIQUESKU001');
        $this->assertTrue($media->short_description == 'This is the body');
        $this->assertTrue($media->category_id == 1);
        $this->assertTrue($media->price == 0);
        $this->assertTrue($media->featured == false);
        $this->assertTrue($media->active == true);
        $this->assertTrue($result == 1);
    }

    public function testFind1()
    {
        $this->testCreateNew(); //Create new first

        $media = Media::find(1);

        $this->assertTrue($media != null);
        $this->assertTrue($media->id == 1);
        $this->assertTrue($media->name == 'This is the title');
    }

    public function testPagniate()
    {
        $medias = Media::paginate(20);
    }

    public function testOrderBy()
    {
        $medias = Media::orderBy('updated_at');
    }

    public function testOrderByThenPaginate()
    {
        $medias = Media::orderBy('updated_at')->paginate(20);
    }

    public function testDestroy()
    {
        $this->testCreateNew(); //Create new first

        $media = Media::find('1');
        $media->delete();

        $result = Media::find('1');

        $this->assertTrue($result == null);
    }

}
