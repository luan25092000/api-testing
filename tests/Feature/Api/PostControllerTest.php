<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Post;

class PostControllerTest extends TestCase
{
    protected $tableName = 'posts';
    protected $posts;
    protected $faker;
    protected $baseUrl;

    // Khi chạy test xong thì sẽ revert database trở về ban đầu
    use DatabaseTransactions;

    /**
     * Hàm này sẽ được gọi trước mỗi hàm test, setup môi trường test
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->faker = \Faker\Factory::create();
        $this->baseUrl = url('/');
        $this->fakeData();
    }

    /**
     * Có thể tạo nhiều data mẫu trong hàm này
     *
     * @return void
     */
    protected function fakeData()
    {
        $this->fakePostData();
    }

    protected function fakePostData()
    {
        $this->posts = Post::factory()->create();
    }

    /**
     * Hàm này dùng để định dạng response trả về
     *
     * @return Array
     */
    protected function getPostStructure()
    {
        return [
            'id',
            'title',
            'content'
        ];
    }

    /**
     * Fake data cho trường hợp store success
     *
     */ 
    protected function getDataStoreSuccess()
    {
        return [
            'title' => $this->faker->regexify('[A-Za-z]{50}'),
            'content' => $this->faker->regexify('[A-Za-z0-9]{255}')
        ];
    }

    /**
     * Fake data cho trường hợp store fail
     *
     */
    protected function getDataStoreFail()
    {
        $dataStoreFail = [
            'title' => $this->faker->randomElement([
                null,
                $this->faker->numberBetween(1, 10),
                $this->faker->regexify('[A-Za-z0-9]{51}')
            ]),
            'content' => $this->faker->randomElement([
                null,
                $this->faker->regexify('[A-Za-z0-9]{256}')
            ]),
        ];
        return $dataStoreFail;
    }

    protected function getDataUpdateSuccess($post)
    {
        return [
            'title' => $this->faker->randomElement([
                $post->title,
                $this->faker->regexify('[A-Za-z]{50}')
            ]),
            'content' => $this->faker->randomElement([
                $post->content,
                $this->faker->regexify('[A-Za-z0-9]{255}')
            ]),
        ];
    }

    protected function getDataUpdateFail()
    {
        $dataUpdateFail = [
            'title' => $this->faker->randomElement([
                null,
                $this->faker->numberBetween(1, 10),
                $this->faker->regexify('[A-Za-z0-9]{51}')
            ]),
            'content' => $this->faker->randomElement([
                null,
                $this->faker->regexify('[A-Za-z0-9]{256}')
            ]),
        ];

        return $dataUpdateFail;
    }

    /**
     * Hàm này dùng gọi API show
     *
     */ 
    protected function apiShow()
    {
        return $this->json(
            'GET',
            "{$this->baseUrl}/api/posts",
        );
    }

    /**
     * Hàm này dùng gọi API store
     *
     */ 
    protected function apiStore($data)
    {
        return $this->json(
            'POST',
            "{$this->baseUrl}/api/post",
            $data
        );
    }

    /**
     * Hàm này dùng gọi API update
     *
     */ 
    protected function apiUpdate($postId, $data)
    {
        return $this->json(
            'POST',
            "{$this->baseUrl}/api/posts/id/{$postId}",
            $data
        );
    }

    /**
     * Hàm này dùng gọi API delete
     *
     */ 
    protected function apiDelete($postId)
    {
        return $this->json(
            'DELETE',
            "{$this->baseUrl}/api/posts/id/{$postId}"
        );
    }

    protected function sendShowRequest($statusCode = 200)
    {
        $response = $this->apiShow();
        $response->assertStatus($statusCode);

        if ($statusCode === 200) {
            $response->assertJsonStructure([
                'data' => [
                  '*' => $this->getPostStructure()
                ]
            ]);
        }
    }

    /**
     * Gửi store request
     *
     */ 
    protected function sendStoreRequest($data, $statusCode = 200)
    {
        $response = $this->apiStore($data);
        $response->assertStatus($statusCode);

        // Check xem đã store thành công chưa
        if ($statusCode === 200) {
            $this->assertDatabaseHas($this->tableName, $data);
            $response->assertJsonStructure([
                'data' => [
                  '*' => $this->getPostStructure()
                ]
            ]);
        }

        return $response;
    }

    /**
     * Gửi update request
     *
     */ 
    protected function sendUpdateRequest($postId, $data, $statusCode = 200)
    {
        $response = $this->apiUpdate($postId, $data);
        $response->assertStatus($statusCode);

        // Check xem đã update thành công chưa
        if ($statusCode === 200) {
            $this->assertDatabaseHas($this->tableName, $data);
            $response->assertJsonStructure([
                'data' => [
                  '*' => $this->getPostStructure()
                ]
            ]);
        }

        return $response;
    }

    /**
     * Gửi delete request
     *
     */ 
    protected function sendDeleteRequest($postId, $statusCode = 200)
    {
        $response = $this->apiDelete($postId);
        $response->assertStatus($statusCode);

        if ($statusCode === 200) {
            $this->assertDatabaseMissing($this->tableName, ['id' => $postId]);
        }

        return $response;
    }

     /**
     * Test show success
     *
     */ 
    public function testShowSuccess()
    {
        $this->sendShowRequest();
    }

    /**
     * Test store success
     *
     */ 
    public function testStoreSuccess()
    {
        $this->sendStoreRequest($this->getDataStoreSuccess());
    }

    /**
     * Test store fail bởi validate
     *
     */ 
    public function testStoreFailByValidate()
    {
        $data = $this->getDataStoreFail();
        $this->sendStoreRequest($data, 422);
    }

    /**
     * Test update success
     *
     */ 
    public function testUpdateSuccess()
    {
        $post = $this->posts->get()[1];
        $data = $this->getDataUpdateSuccess($post);

        $this->sendUpdateRequest($post->id, $data);
    }

    /**
     * Test update fail by validate
     *
     */ 
    public function testUpdateFailByValidation()
    {
        $post = $this->posts->get()[1];
        $data = $this->getDataUpdateFail();

        $this->sendUpdateRequest($post->id, $data, 422);
    }

    /**
     * Test delete success
     *
     */ 
    public function testDeleteSuccess()
    {
        $this->fakePostData();
        $post = $this->faker->randomElement($this->posts->get());
        $this->sendDeleteRequest($post->id);
    }


    /**
     * Test delete fail by param
     *
     */ 
    public function testDeleteFailByParam()
    {
        $failParam = $this->faker->randomElement([
            'text',
            -1,
        ]);
        $this->sendDeleteRequest($failParam, 404);
    }
}
