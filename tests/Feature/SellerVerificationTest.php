<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerVerificationTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * Buat user dengan role admin.
     */
    private function createAdmin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    /**
     * Buat user dengan role seller.
     */
    private function createSellerUser(): User
    {
        return User::factory()->create(['role' => 'seller']);
    }

    /**
     * Buat user dengan role buyer.
     */
    private function createBuyerUser(): User
    {
        return User::factory()->create(['role' => 'buyer']);
    }

    /**
     * Buat record Seller di database untuk user tertentu.
     */
    private function createSellerRecord(User $user, array $overrides = []): Seller
    {
        return Seller::create(array_merge([
            'user_id'             => $user->id,
            'store_name'          => 'Toko Test',
            'address'             => 'Jl. Test No. 1',
            'verification_status' => 'pending',
        ], $overrides));
    }

    // =========================================================================
    // A. DOCUMENT UPLOAD — Validasi File
    // =========================================================================


    public function test_seller_can_upload_valid_pdf_document(): void
    {
        Storage::fake('local');

        $user = $this->createSellerUser();
        $seller = $this->createSellerRecord($user);

        $response = $this->actingAs($user)
            ->post(route('seller.upload-documents'), [
                'document' => UploadedFile::fake()->create('dokumen.pdf', 1024, 'application/pdf'),
            ]);

        $response->assertRedirect(route('seller.profile'));
        $response->assertSessionHas('success');

        $seller->refresh();
        $this->assertNotNull($seller->document_path);
        $this->assertEquals('pending', $seller->verification_status);
    }


    public function test_seller_can_upload_valid_jpg_document(): void
    {
        Storage::fake('local');

        $user = $this->createSellerUser();
        $seller = $this->createSellerRecord($user);

        $response = $this->actingAs($user)
            ->post(route('seller.upload-documents'), [
                'document' => UploadedFile::fake()->image('ktp.jpg', 800, 600),
            ]);

        $response->assertRedirect(route('seller.profile'));
        $seller->refresh();
        $this->assertNotNull($seller->document_path);
    }


    public function test_seller_can_upload_valid_png_document(): void
    {
        Storage::fake('local');

        $user = $this->createSellerUser();
        $seller = $this->createSellerRecord($user);

        $response = $this->actingAs($user)
            ->post(route('seller.upload-documents'), [
                'document' => UploadedFile::fake()->image('siup.png', 800, 600),
            ]);

        $response->assertRedirect(route('seller.profile'));
        $seller->refresh();
        $this->assertNotNull($seller->document_path);
    }


    public function test_upload_rejects_invalid_file_extension(): void
    {
        $user = $this->createSellerUser();
        $this->createSellerRecord($user);

        $response = $this->actingAs($user)
            ->post(route('seller.upload-documents'), [
                'document' => UploadedFile::fake()->create('malware.exe', 500, 'application/x-msdownload'),
            ]);

        $response->assertSessionHasErrors('document');
    }


    public function test_upload_rejects_zip_file(): void
    {
        $user = $this->createSellerUser();
        $this->createSellerRecord($user);

        $response = $this->actingAs($user)
            ->post(route('seller.upload-documents'), [
                'document' => UploadedFile::fake()->create('arsip.zip', 500, 'application/zip'),
            ]);

        $response->assertSessionHasErrors('document');
    }


    public function test_upload_rejects_file_exceeding_5mb(): void
    {
        $user = $this->createSellerUser();
        $this->createSellerRecord($user);

        $response = $this->actingAs($user)
            ->post(route('seller.upload-documents'), [
                'document' => UploadedFile::fake()->create('besar.pdf', 6000, 'application/pdf'), // 6MB
            ]);

        $response->assertSessionHasErrors('document');
    }


    public function test_upload_rejects_empty_file_field(): void
    {
        $user = $this->createSellerUser();
        $this->createSellerRecord($user);

        $response = $this->actingAs($user)
            ->post(route('seller.upload-documents'), []);

        $response->assertSessionHasErrors('document');
    }


    public function test_document_path_is_stored_in_database_after_upload(): void
    {
        Storage::fake('local');

        $user = $this->createSellerUser();
        $seller = $this->createSellerRecord($user);

        $this->actingAs($user)
            ->post(route('seller.upload-documents'), [
                'document' => UploadedFile::fake()->create('nib.pdf', 1024, 'application/pdf'),
            ]);

        $seller->refresh();
        $this->assertNotNull($seller->document_path);
        $this->assertStringContainsString('documents/', $seller->document_path);
    }


    public function test_uploading_new_document_replaces_old_document_path(): void
    {
        Storage::fake('local');

        $user = $this->createSellerUser();
        $seller = $this->createSellerRecord($user, [
            'document_path' => 'documents/old-doc.pdf',
        ]);
        Storage::put('documents/old-doc.pdf', 'fake content');

        $this->actingAs($user)
            ->post(route('seller.upload-documents'), [
                'document' => UploadedFile::fake()->create('new-doc.pdf', 1024, 'application/pdf'),
            ]);

        $seller->refresh();
        $this->assertNotEquals('documents/old-doc.pdf', $seller->document_path);
    }


    public function test_re_uploading_document_resets_status_to_pending(): void
    {
        Storage::fake('local');

        $user = $this->createSellerUser();
        $seller = $this->createSellerRecord($user, [
            'verification_status' => 'rejected',
            'rejection_reason'    => 'Dokumen tidak jelas',
        ]);

        $this->actingAs($user)
            ->post(route('seller.upload-documents'), [
                'document' => UploadedFile::fake()->create('ulang.pdf', 1024, 'application/pdf'),
            ]);

        $seller->refresh();
        $this->assertEquals('pending', $seller->verification_status);
        $this->assertNull($seller->rejection_reason);
        $this->assertNull($seller->verified_at);
    }

    // =========================================================================
    // B. ADMIN VERIFICATION — Approve / Reject / Suspend
    // =========================================================================


    public function test_admin_can_approve_seller(): void
    {
        $admin = $this->createAdmin();
        $sellerUser = $this->createSellerUser();
        $seller = $this->createSellerRecord($sellerUser, [
            'verification_status' => 'pending',
            'document_path'       => 'documents/valid.pdf',
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.sellers.verify', $seller->id), [
                'status_action' => 'approved',
            ]);

        $response->assertRedirect(route('admin.stores'));

        $seller->refresh();
        $this->assertEquals('approved', $seller->verification_status);
        $this->assertNotNull($seller->verified_at);
        $this->assertNull($seller->rejection_reason);
    }


    public function test_admin_can_reject_seller_with_reason(): void
    {
        $admin = $this->createAdmin();
        $sellerUser = $this->createSellerUser();
        $seller = $this->createSellerRecord($sellerUser, [
            'verification_status' => 'pending',
            'document_path'       => 'documents/blurry.jpg',
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.sellers.verify', $seller->id), [
                'status_action'    => 'rejected',
                'rejection_reason' => 'Foto dokumen buram dan tidak terbaca.',
            ]);

        $response->assertRedirect(route('admin.stores'));

        $seller->refresh();
        $this->assertEquals('rejected', $seller->verification_status);
        $this->assertEquals('Foto dokumen buram dan tidak terbaca.', $seller->rejection_reason);
        $this->assertNull($seller->verified_at);
    }


    public function test_admin_cannot_reject_without_providing_reason(): void
    {
        $admin = $this->createAdmin();
        $sellerUser = $this->createSellerUser();
        $seller = $this->createSellerRecord($sellerUser, [
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.sellers.verify', $seller->id), [
                'status_action' => 'rejected',
                // rejection_reason tidak diisi
            ]);

        $response->assertSessionHasErrors('rejection_reason');
    }


    public function test_admin_can_suspend_active_seller(): void
    {
        $admin = $this->createAdmin();
        $sellerUser = $this->createSellerUser();
        $seller = $this->createSellerRecord($sellerUser, [
            'verification_status' => 'approved',
            'verified_at'         => now(),
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.sellers.verify', $seller->id), [
                'status_action'    => 'suspended',
                'rejection_reason' => 'Pelanggaran standar kualitas.',
            ]);

        $response->assertRedirect(route('admin.stores'));

        $seller->refresh();
        $this->assertEquals('suspended', $seller->verification_status);
        $this->assertEquals('Pelanggaran standar kualitas.', $seller->rejection_reason);
    }


    public function test_admin_cannot_suspend_without_providing_reason(): void
    {
        $admin = $this->createAdmin();
        $sellerUser = $this->createSellerUser();
        $seller = $this->createSellerRecord($sellerUser, [
            'verification_status' => 'approved',
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('admin.sellers.verify', $seller->id), [
                'status_action' => 'suspended',
                // rejection_reason tidak diisi
            ]);

        $response->assertSessionHasErrors('rejection_reason');
    }


    public function test_approved_seller_has_verified_at_timestamp(): void
    {
        $admin = $this->createAdmin();
        $sellerUser = $this->createSellerUser();
        $seller = $this->createSellerRecord($sellerUser);

        $this->actingAs($admin)
            ->patch(route('admin.sellers.verify', $seller->id), [
                'status_action' => 'approved',
            ]);

        $seller->refresh();
        $this->assertNotNull($seller->verified_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $seller->verified_at);
    }


    public function test_rejected_seller_has_null_verified_at(): void
    {
        $admin = $this->createAdmin();
        $sellerUser = $this->createSellerUser();
        $seller = $this->createSellerRecord($sellerUser, [
            'verification_status' => 'approved',
            'verified_at'         => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.sellers.verify', $seller->id), [
                'status_action'    => 'rejected',
                'rejection_reason' => 'Dokumen kedaluwarsa.',
            ]);

        $seller->refresh();
        $this->assertNull($seller->verified_at);
    }

    // =========================================================================
    // C. ENDPOINT PROTECTION — Role-based Access Control
    // =========================================================================


    public function test_seller_cannot_access_admin_verify_endpoint(): void
    {
        $sellerUser = $this->createSellerUser();
        $seller = $this->createSellerRecord($sellerUser);

        $response = $this->actingAs($sellerUser)
            ->patch(route('admin.sellers.verify', $seller->id), [
                'status_action' => 'approved',
            ]);

        $response->assertForbidden();
    }


    public function test_buyer_cannot_access_admin_verify_endpoint(): void
    {
        $buyer = $this->createBuyerUser();
        $sellerUser = $this->createSellerUser();
        $seller = $this->createSellerRecord($sellerUser);

        $response = $this->actingAs($buyer)
            ->patch(route('admin.sellers.verify', $seller->id), [
                'status_action' => 'approved',
            ]);

        $response->assertForbidden();
    }


    public function test_unauthenticated_user_cannot_access_admin_verify_endpoint(): void
    {
        $sellerUser = $this->createSellerUser();
        $seller = $this->createSellerRecord($sellerUser);

        $response = $this->patch(route('admin.sellers.verify', $seller->id), [
            'status_action' => 'approved',
        ]);

        $response->assertRedirect(route('login'));
    }


    public function test_buyer_cannot_access_seller_upload_endpoint(): void
    {
        $buyer = $this->createBuyerUser();

        $response = $this->actingAs($buyer)
            ->post(route('seller.upload-documents'), [
                'document' => UploadedFile::fake()->create('test.pdf', 500, 'application/pdf'),
            ]);

        $response->assertForbidden();
    }

    // =========================================================================
    // D. CATALOG ACCESS RESTRICTION — Middleware verified_seller
    // =========================================================================


    public function test_approved_seller_can_access_catalog_products(): void
    {
        $user = $this->createSellerUser();
        $this->createSellerRecord($user, [
            'verification_status' => 'approved',
            'verified_at'         => now(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('seller.products'));

        $response->assertOk();
    }


    public function test_pending_seller_cannot_access_catalog_products(): void
    {
        $user = $this->createSellerUser();
        $this->createSellerRecord($user, [
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->get(route('seller.products'));

        $response->assertRedirect(route('seller.profile'));
    }


    public function test_rejected_seller_cannot_access_catalog_products(): void
    {
        $user = $this->createSellerUser();
        $this->createSellerRecord($user, [
            'verification_status' => 'rejected',
            'rejection_reason'    => 'Dokumen tidak valid.',
        ]);

        $response = $this->actingAs($user)
            ->get(route('seller.products'));

        $response->assertRedirect(route('seller.profile'));
    }


    public function test_suspended_seller_cannot_access_catalog_products(): void
    {
        $user = $this->createSellerUser();
        $this->createSellerRecord($user, [
            'verification_status' => 'suspended',
        ]);

        $response = $this->actingAs($user)
            ->get(route('seller.products'));

        $response->assertRedirect(route('seller.profile'));
    }


    public function test_seller_without_seller_record_cannot_access_catalog(): void
    {
        $user = $this->createSellerUser();
        // Tidak membuat Seller record sama sekali

        $response = $this->actingAs($user)
            ->get(route('seller.products'));

        $response->assertRedirect(route('seller.profile'));
    }


    public function test_pending_seller_cannot_store_product(): void
    {
        Storage::fake('public');

        $user = $this->createSellerUser();
        $this->createSellerRecord($user, [
            'verification_status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->post(route('seller.product.store'), [
                'name'           => 'Nasi Goreng',
                'category_id'    => 1,
                'base_price'     => 15000,
                'discount_price' => 10000,
                'qty_reg'        => 5,
                'image'          => UploadedFile::fake()->image('nasi.jpg'),
            ]);

        $response->assertRedirect(route('seller.profile'));
    }

    // =========================================================================
    // E. DEFAULT STATUS — Seller baru default pending
    // =========================================================================


    public function test_new_seller_has_default_pending_status(): void
    {
        Storage::fake('local');

        $user = $this->createSellerUser();

        // Upload dokumen tanpa seller record sebelumnya — harus otomatis buat Seller
        $this->actingAs($user)
            ->post(route('seller.upload-documents'), [
                'document' => UploadedFile::fake()->create('nib.pdf', 1024, 'application/pdf'),
            ]);

        $seller = Seller::where('user_id', $user->id)->first();
        $this->assertNotNull($seller);
        $this->assertEquals('pending', $seller->verification_status);
    }

    // =========================================================================
    // F. DATABASE INTEGRITY
    // =========================================================================


    public function test_verification_status_changes_do_not_corrupt_other_seller_data(): void
    {
        $admin = $this->createAdmin();
        $sellerUser = $this->createSellerUser();
        $seller = $this->createSellerRecord($sellerUser, [
            'store_name'          => 'Warung Makan ABC',
            'address'             => 'Jl. Merdeka No. 45',
            'document_path'       => 'documents/ktp.jpg',
            'verification_status' => 'pending',
        ]);

        // Approve
        $this->actingAs($admin)
            ->patch(route('admin.sellers.verify', $seller->id), [
                'status_action' => 'approved',
            ]);

        $seller->refresh();
        $this->assertEquals('approved', $seller->verification_status);
        // Data lain tetap utuh
        $this->assertEquals('Warung Makan ABC', $seller->store_name);
        $this->assertEquals('Jl. Merdeka No. 45', $seller->address);
        $this->assertEquals('documents/ktp.jpg', $seller->document_path);
    }


    public function test_rejection_reason_is_stored_correctly_in_database(): void
    {
        $admin = $this->createAdmin();
        $sellerUser = $this->createSellerUser();
        $seller = $this->createSellerRecord($sellerUser);

        $reason = 'Dokumen KTP tidak sesuai dengan nama toko yang didaftarkan. Mohon unggah ulang dokumen yang benar.';

        $this->actingAs($admin)
            ->patch(route('admin.sellers.verify', $seller->id), [
                'status_action'    => 'rejected',
                'rejection_reason' => $reason,
            ]);

        $this->assertDatabaseHas('sellers', [
            'id'                  => $seller->id,
            'verification_status' => 'rejected',
            'rejection_reason'    => $reason,
        ]);
    }


    public function test_approval_clears_previous_rejection_reason(): void
    {
        $admin = $this->createAdmin();
        $sellerUser = $this->createSellerUser();
        $seller = $this->createSellerRecord($sellerUser, [
            'verification_status' => 'rejected',
            'rejection_reason'    => 'Foto buram.',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.sellers.verify', $seller->id), [
                'status_action' => 'approved',
            ]);

        $seller->refresh();
        $this->assertEquals('approved', $seller->verification_status);
        $this->assertNull($seller->rejection_reason);
    }

    // =========================================================================
    // G. ADMIN — View Document
    // =========================================================================


    public function test_admin_can_download_seller_document(): void
    {
        Storage::fake('local');

        $admin = $this->createAdmin();
        $sellerUser = $this->createSellerUser();
        $seller = $this->createSellerRecord($sellerUser, [
            'document_path' => 'documents/ktp.pdf',
        ]);
        Storage::put('documents/ktp.pdf', 'fake pdf content');

        $response = $this->actingAs($admin)
            ->get(route('admin.sellers.document', $seller->id));

        $response->assertOk();
        $response->assertDownload('ktp.pdf');
    }


    public function test_admin_gets_404_for_missing_document(): void
    {
        $admin = $this->createAdmin();
        $sellerUser = $this->createSellerUser();
        $seller = $this->createSellerRecord($sellerUser, [
            'document_path' => null,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.sellers.document', $seller->id));

        $response->assertNotFound();
    }
}
