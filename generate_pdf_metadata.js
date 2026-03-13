const fs = require('fs');
const path = require('path');

const RESOURCES_DIR = path.join(__dirname, 'apilageai.lk/doc_text/resources');

function generateMetadataForPDF(pdfPath, grade, subject) {
  try {
    const filename = path.basename(pdfPath);

    // Generate chapter title from filename
    const chapterMatch = filename.match(/chapter_(\d+)/i);
    const chapterNum = chapterMatch ? chapterMatch[1] : '1';
    const displayName = `Chapter ${parseInt(chapterNum)}: ${subject.charAt(0).toUpperCase() + subject.slice(1)}`;

    // Generate unique ID
    const timestamp = Date.now();
    const id = `${subject}-ch${chapterNum}-${timestamp}`;

    // Create sections
    const sections = [
      { page: 1, title: `Introduction - Chapter ${chapterNum}` }
    ];

    if (chapterNum > 5) {
      sections.push({ page: 5, title: `Core Concepts - Chapter ${chapterNum}` });
    }

    if (chapterNum > 10) {
      sections.push({ page: 10, title: `Advanced Topics - Chapter ${chapterNum}` });
    }

    // Create metadata object
    const metadata = {
      id,
      filename,
      display_name: displayName,
      grade: parseInt(grade),
      subject: subject.toLowerCase(),
      sections,
      createdAt: timestamp
    };

    // Write metadata JSON
    const jsonPath = pdfPath.replace('.pdf', '.json');
    fs.writeFileSync(jsonPath, JSON.stringify(metadata, null, 2));

    // Create placeholder text file (will be populated with actual PDF text extraction later)
    const txtPath = pdfPath.replace('.pdf', '.txt');
    const placeholder = `Chapter ${parseInt(chapterNum)}: ${displayName}\n\nThis is a resource file for Grade ${grade} ${subject}. Content will be extracted from the PDF for context-based AI responses.\n\nSource: ${filename}`;
    fs.writeFileSync(txtPath, placeholder);

    console.log(`✅ ${filename}`);
    console.log(`   - JSON: ${path.basename(jsonPath)}`);
    console.log(`   - Text: ${path.basename(txtPath)}`);

    return { success: true, filename, id };
  } catch (error) {
    console.error(`❌ Error processing ${path.basename(pdfPath)}: ${error.message}`);
    return { success: false, filename: path.basename(pdfPath), error: error.message };
  }
}

async function processGradeSubject(grade, subject) {
  const dirPath = path.join(RESOURCES_DIR, `grade_${grade}`, subject.toLowerCase());

  if (!fs.existsSync(dirPath)) {
    console.log(`⚠️  Directory not found: ${dirPath}`);
    return;
  }

  console.log(`\n📚 Processing Grade ${grade} - ${subject}`);
  console.log('='.repeat(50));

  const files = fs.readdirSync(dirPath);
  const pdfFiles = files.filter(f => f.endsWith('.pdf')).sort();

  if (pdfFiles.length === 0) {
    console.log('No PDF files found.');
    return;
  }

  console.log(`Found ${pdfFiles.length} PDF files\n`);

  const results = [];
  for (const pdfFile of pdfFiles) {
    const pdfPath = path.join(dirPath, pdfFile);
    const result = generateMetadataForPDF(pdfPath, grade, subject);
    results.push(result);
  }

  const successful = results.filter(r => r.success).length;
  console.log(`\n✨ Successfully processed: ${successful}/${pdfFiles.length} files`);
}

async function main() {
  console.log('🚀 PDF Metadata Generator');
  console.log('='.repeat(50));

  const grades = [10, 11];
  const subjects = ['maths', 'science'];

  for (const grade of grades) {
    for (const subject of subjects) {
      await processGradeSubject(grade, subject);
    }
  }

  console.log('\n' + '='.repeat(50));
  console.log('✅ Metadata files generated!');
  console.log('\n📝 Next step: Extract actual PDF text content into .txt files');
  console.log('   You can use online PDF to text tools or pdftotext command');
}

main().catch(console.error);
