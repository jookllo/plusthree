<?php

namespace App\Filament\Resources\JobListings\Schemas;

use App\Enums\EmploymentType;
use App\Enums\PublicationStatus;
use App\Enums\WorkMode;
use App\Models\Category;
use App\Models\JobListing;
use App\Support\InlineImage;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class JobListingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(180),
                TextInput::make('slug')
                    ->unique(ignoreRecord: true)
                    ->maxLength(200)
                    ->disabled(fn (?JobListing $record): bool => $record?->isPublished() ?? false)
                    ->helperText('Leave blank to generate from the title. Published slugs are locked until redirects exist.'),
                Select::make('employment_type')
                    ->options(EmploymentType::options())
                    ->required(),
                Select::make('work_mode')
                    ->options(WorkMode::options())
                    ->required(),
                TextInput::make('location_city')
                    ->maxLength(120),
                TextInput::make('location_country')
                    ->maxLength(80)
                    ->helperText('Where the role is based or who may apply. Kept out of the public search box by design.'),
                RichEditor::make('description')
                    ->label('Role description')
                    ->required()
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('jobs/inline')
                    ->fileAttachmentsVisibility('public')
                    ->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->fileAttachmentsMaxSize(4096)
                    ->saveUploadedFileAttachmentUsing(
                        fn (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string => InlineImage::store($file, 'jobs/inline')
                    )
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'link'],
                        ['h2', 'h3'],
                        ['bulletList', 'orderedList', 'blockquote'],
                        ['attachFiles'],
                        ['undo', 'redo'],
                    ])
                    ->floatingToolbars([
                        'paragraph' => ['bold', 'italic', 'underline', 'link'],
                    ])
                    ->helperText('What the role involves, the team and the terms. Images are inserted where the cursor is.'),
                RichEditor::make('requirements')
                    ->label('What you’ll bring')
                    ->columnSpanFull()
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory('jobs/inline')
                    ->fileAttachmentsVisibility('public')
                    ->fileAttachmentsAcceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->fileAttachmentsMaxSize(4096)
                    ->saveUploadedFileAttachmentUsing(
                        fn (\Livewire\Features\SupportFileUploads\TemporaryUploadedFile $file): string => InlineImage::store($file, 'jobs/inline')
                    )
                    ->toolbarButtons([
                        ['bold', 'italic', 'underline', 'link'],
                        ['bulletList', 'orderedList'],
                        ['undo', 'redo'],
                    ])
                    ->helperText('Optional. Skills, experience and qualifications.'),
                FileUpload::make('featured_image_path')
                    ->label('Featured image')
                    ->image()
                    ->disk('public')
                    ->directory('jobs')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(4096)
                    ->imageAspectRatio('16:9')
                    ->automaticallyCropImagesToAspectRatio()
                    ->automaticallyResizeImagesToWidth('1600')
                    ->automaticallyResizeImagesToHeight('900')
                    ->automaticallyUpscaleImagesWhenResizing(false)
                    ->helperText('Optional. Shown at the top of the job page. Cropped to 16:9 and resized to 1600×900.')
                    ->columnSpanFull(),
                TextInput::make('featured_image_alt')
                    ->label('Image alt text')
                    ->maxLength(255)
                    ->required(fn (Get $get): bool => filled($get('featured_image_path')))
                    ->helperText('Describe the image for screen readers. Required when an image is set.'),
                TextInput::make('application_url')
                    ->label('Application URL')
                    ->url()
                    ->maxLength(2048)
                    ->helperText('http or https only. Other schemes are rejected when the listing is displayed.'),
                TextInput::make('application_email')
                    ->email(),
                Select::make('categories')
                    ->label('Sectors')
                    ->relationship(
                        'categories',
                        'name',
                        modifyQueryUsing: fn (Builder $query) => $query->where('kind', Category::KIND_JOB),
                    )
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->helperText('How the role is filed on the job board. Keep to one sector where possible.'),
                Select::make('tags')
                    ->relationship('tags', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->helperText('Optional cross-cutting labels such as Senior or Visa sponsorship.'),
                Select::make('status')
                    ->options(PublicationStatus::options())
                    ->default(PublicationStatus::Draft)
                    ->required()
                    ->helperText('Published makes the listing live immediately. Archived removes it from every list and the sitemap, but its page stays readable for anyone with the old link.'),
                DatePicker::make('closing_at')
                    ->label('Closing date')
                    ->helperText('Applications close at the end of this day, Nairobi time. Blank means no deadline. Roles inside the last week show a “Closing soon” warning.'),
                TextInput::make('seo_title')
                    ->label('SEO title')
                    ->maxLength(255)
                    ->helperText('Falls back to the role title.'),
                Textarea::make('seo_description')
                    ->label('SEO description')
                    ->rows(2)
                    ->maxLength(320)
                    ->helperText('Falls back to the opening text of the description.'),
            ]);
    }
}
