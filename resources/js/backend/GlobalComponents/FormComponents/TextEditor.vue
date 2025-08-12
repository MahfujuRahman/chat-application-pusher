<template>
  <div>
    <div :id="name"></div>
  </div>
</template>

<script>
export default {
  props: {
    name: {
      required: true,
      type: String,
    },
    value: {
      type: String,
      default: "",
    },
  },
  emits: ["input"],
  data() {
    return {
      isInitialized: false,
    };
  },
  created() {
    this.initialiteSummerNote(this.name);
    // Wait for Summernote to initialize before setting up tooltips
    setTimeout(() => {
      this.setupTooltips();
    }, 1500);
  },
  mounted() {
    // Set initial value after component is mounted
    if (this.value) {
      this.setInitialValue();
    }
  },
  watch: {
    value(newValue) {
      // Update summernote content when value prop changes
      if (this.isInitialized) {
        this.updateContent(newValue);
      }
    },
  },
  beforeUnmount() {
    // Clean up Summernote instance to prevent conflicts
    const element = $(`#${this.name}`);
    if (element.length && element.summernote) {
      element.summernote("destroy");
    }
  },
  methods: {
    initialiteSummerNote(id) {
      setTimeout(() => {
        $(`#${id}`).summernote({
          height: 216,
          tabsize: 2,
          callbacks: {
            onChange: (contents) => {
              // Emit the change event to parent component
              this.$emit("input", contents);
            },
            onInit: () => {
              this.isInitialized = true;
              // Set initial value after initialization
              if (this.value) {
                this.updateContent(this.value);
              }
            },
          },
        });
      }, 1000);
    },

    setInitialValue() {
      // Set initial value with retry logic
      setTimeout(() => {
        if ($(`#${this.name}`).summernote && this.value) {
          this.updateContent(this.value);
        }
      }, 1200);
    },

    updateContent(content) {
      // Safely update summernote content
      try {
        const element = $(`#${this.name}`);
        if (element.length && element.summernote) {
          const currentContent = element.summernote("code");
          if (currentContent !== content) {
            element.summernote("code", content || "");
          }
        }
      } catch (error) {
        console.error("Error updating summernote content:", error);
      }
    },
    setupTooltips() {
      // Get the specific editor container for this instance
      const editorContainer = document.querySelector(
        `#${this.name}`
      ).parentElement;

      // Setup tooltips only within this editor's container
      this.summerNoteTooltip("Style", "dropdown-style", editorContainer);
      this.summerNoteTooltip(
        "Font Family",
        "dropdown-fontname",
        editorContainer
      );
      this.summerNoteTooltip("More Color", "note-color", editorContainer);
      this.summerNoteTooltip("Paragraph", "note-color", editorContainer);
      this.summerNoteTooltip("Table", "note-table", editorContainer);
    },
    summerNoteTooltip(style, classname, container = document) {
      // Find elements only within the specific editor container
      let target = container.querySelector(
        `[data-bs-original-title="${style}"]`
      );
      let targetClass = container.querySelector(`.${classname}`);

      if (target && targetClass) {
        // Remove any existing event listeners to prevent duplicates
        target.removeEventListener("click", this.tooltipClickHandler);

        // Add event listener with a bound handler
        target.addEventListener("click", (event) => {
          event.stopPropagation();
          targetClass.classList.toggle("show");

          if (classname === "note-color" || classname === "note-table") {
            const nextSibling = target.nextElementSibling;
            if (nextSibling) {
              nextSibling.classList.toggle("show");
            }
          }
        });
      }
    },
  },
};
</script>

<style>
.popover-content.note-children-container {
  background: gray;
}
</style>
